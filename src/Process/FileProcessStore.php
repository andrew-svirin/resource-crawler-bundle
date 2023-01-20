<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskPacker;
use Symfony\Component\Lock\LockFactory;

/**
 * Primitive store in the file.
 * Very limited usage.
 */
final class FileProcessStore extends ProcessStore implements ProcessStoreInterface
{
  public function __construct(
    private readonly string $fileStoreDir,
    readonly bool $isLockable,
    private readonly TaskPacker $taskPacker,
    private readonly TaskFactory $taskFactory,
    readonly ?LockFactory $lockFactory = null
  ) {
    parent::__construct($isLockable, $lockFactory);
  }

  /**
   * @param array<string, array<string, string>> $processData
   */
  public function taskExists(array $processData, string $taskHash): bool
  {
    foreach (CrawlingTask::ALL_STATUSES as $status) {
      if (in_array($taskHash, array_keys($processData[$status]))) {
        return true;
      }
    }

    return false;
  }

  public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    $taskHash = $this->genPathHash($task->getNode()->getUri()->getPath());
    $pkdTask  = $this->packTask($task);

    $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

    $op = new UpdateProcessDataClosure($this, function (array $processData) use ($task, $taskHash, $pkdTask): ?array {
      if ($this->taskExists($processData, $taskHash)) {
        return null;
      }

      $processData[$task->getStatus()][$taskHash] = $pkdTask;

      return $processData;
    });

    return $this->safeUpdateProcessData($process, $op);
  }

  public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
  {
    $packedTask = null;

    $op = new UpdateProcessDataClosure($this, function (array $processData) use (&$packedTask) {
      $taskHash = array_key_last($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

      if (empty($taskHash)) {
        return null;
      }

      $packedTask = array_pop($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

      $processData[CrawlingTask::STATUS_IN_PROCESS][$taskHash] = $packedTask;

      return $processData;
    });

    $this->safeUpdateProcessData($process, $op);

    if (null === $packedTask) {
      return null;
    }

    $task = $this->unpackTask($process, $packedTask);

    $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

    return $task;
  }

  public function pushProcessedTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_PROCESSED);
  }

  public function pushIgnoredTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_IGNORED);
  }

  public function pushErroredTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_ERRORED);
  }

  public function revertTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    $taskHash = $this->genPathHash($task->getNode()->getUri()->getPath());

    $fptHashes = [];
    foreach ($task->getPushedForProcessingPaths() as $path) {
      $fptHashes[] = $this->genPathHash($path);
    }

    $op = new UpdateProcessDataClosure($this, function (array $processData) use ($task, $taskHash, $fptHashes) {
      $packedTask = $processData[$task->getStatus()][$taskHash];

      unset($processData[$task->getStatus()][$taskHash]);

      // Put indexed element to the beginning of the array.
      $processData[CrawlingTask::STATUS_FOR_PROCESSING] = [$taskHash => $packedTask] +
        $processData[CrawlingTask::STATUS_FOR_PROCESSING];

      // Remove tasks those were pushed for processing while crawled.
      foreach ($fptHashes as $fptHash) {
        unset($processData[CrawlingTask::STATUS_FOR_PROCESSING][$fptHash]);
      }

      return $processData;
    });

    $update = $this->safeUpdateProcessData($process, $op);

    $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

    return $update;
  }

  private function pushTask(CrawlingProcess $process, CrawlingTask $task, string $status): bool
  {
    $taskHash = $this->genPathHash($task->getNode()->getUri()->getPath());

    $op = new UpdateProcessDataClosure($this, function (array $processData) use ($task, $status, $taskHash) {
      $packedTask = $processData[$task->getStatus()][$taskHash];

      $packedTask['code'] = $task->getNode()->getResponse()?->getCode();

      unset($processData[$task->getStatus()][$taskHash]);

      $processData[$status][$taskHash] = $packedTask;

      return $processData;
    });

    $update = $this->safeUpdateProcessData($process, $op);

    $task->setStatus($status);

    return $update;
  }

  public function deleteProcess(CrawlingProcess $process): bool
  {
    return $this->safeDeleteProcessData($process);
  }

  private function safeUpdateProcessData(CrawlingProcess $process, UpdateProcessDataClosure $closure): bool
  {
    $op = new OperateStoreClosure($this, function () use ($process, $closure): bool {
      $processData = $this->readProcessData($process);

      $processData = $closure->call($processData);

      if (null === $processData) {
        return false;
      }

      return $this->writeProcessData($process, $processData);
    });

    return $this->operateStore($op);
  }

  private function safeDeleteProcessData(CrawlingProcess $process): bool
  {
    $op = new OperateStoreClosure($this, function () use ($process) {
      return $this->deleteProcessData($process);
    });

    return $this->operateStore($op);
  }

  /**
   * @return array<string, array<string, string|array<string, string>>>
   */
  private function readProcessData(CrawlingProcess $process): array
  {
    $content = $this->readProcessContent($process);

    return $content ? json_decode($content, true) : $this->defaultProcessData();
  }

  /**
   * @param array<string, array<string, string|array<string, string>>> $processData
   */
  private function writeProcessData(CrawlingProcess $process, array $processData): bool
  {
    $content = json_encode($processData, JSON_PRETTY_PRINT);

    return $this->writeProcessContent($process, $content);
  }

  private function deleteProcessData(CrawlingProcess $process): bool
  {
    return $this->deleteProcessContent($process);
  }

  private function readProcessContent(CrawlingProcess $process): ?string
  {
    $filename = $this->getProcessFilename($process);

    return $this->readContent($filename);
  }

  private function writeProcessContent(CrawlingProcess $process, string $content): bool
  {
    $filename = $this->getProcessFilename($process);

    $this->prepareFile($filename);

    return $this->writeContent($filename, $content);
  }

  private function deleteProcessContent(CrawlingProcess $process): bool
  {
    $filename = $this->getProcessFilename($process);

    return $this->deleteContent($filename);
  }

  private function readContent(string $filename): ?string
  {
    return file_exists($filename) ? file_get_contents($filename) : null;
  }

  private function writeContent(string $filename, string $content): bool
  {
    if (!file_exists($filename)) {
      return false;
    }

    return (bool) file_put_contents($filename, $content);
  }

  private function deleteContent(string $filename): bool
  {
    if (!file_exists($filename)) {
      return false;
    }

    return unlink($filename);
  }

  private function prepareFile(string $filename): void
  {
    $fileDir = dirname($filename);

    if (!file_exists($fileDir)) {
      mkdir($fileDir, 0775, true);
    }

    touch($filename);
  }

  /**
   * @return array<string, array<null>>
   */
  private function defaultProcessData(): array
  {
    $default = [];

    foreach (CrawlingTask::ALL_STATUSES as $status) {
      $default[$status] = [];
    }

    return $default;
  }

  private function getProcessFilename(CrawlingProcess $process): string
  {
    return $this->fileStoreDir . '/' . $process->getId() . '.json';
  }

  private function genPathHash(string $path): string
  {
    return hash('sha256', $path);
  }

  /**
   * @param \AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask $task
   * @return array<string, string|array<string, string>>
   */
  private function packTask(CrawlingTask $task): array
  {
    $nodeType = $this->taskPacker->packNodeType($task->getNode());
    $uriType  = $this->taskPacker->packUriType($task->getNode()->getUri());

    return [
      'type' => $nodeType,
      'uri'  => [
        'type' => $uriType,
        'path' => $task->getNode()->getUri()->getPath(),
      ],
      'code' => $task->getNode()->getResponse()?->getCode(),
    ];
  }

  /**
   * @param array<string, string|array<string, string>> $packedTask
   */
  private function unpackTask(CrawlingProcess $process, array $packedTask): CrawlingTask
  {
    $uri  = $this->taskPacker->unpackUri($packedTask['uri']['type'], $packedTask['uri']['path']);
    $node = $this->taskPacker->unpackNode($packedTask['type'], $uri);

    return $this->taskFactory->create($process, $node);
  }

  public function countTasks(CrawlingProcess $process): array
  {
    $counts = [];

    $op = new OperateStoreClosure($this, function () use ($process, &$counts) {
      $processData = $this->readProcessData($process);
      foreach (CrawlingTask::ALL_STATUSES as $status) {
        $counts[$status] = count($processData[$status]);
      }

      return true;
    });

    $this->operateStore($op);

    return $counts;
  }
}
