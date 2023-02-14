<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Store\File;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\OperateStoreClosure;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStore;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStoreInterface;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskPacker;
use LogicException;
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

  private function taskExists(ProcessData $processData, string $taskHash): bool
  {
    foreach (CrawlingTask::ALL_STATUSES as $status) {
      if (in_array($taskHash, array_keys($processData->data[$status]))) {
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

    $op = new UpdateProcessDataClosure(
      $this,
      function (ProcessData $processData) use ($task, $taskHash, $pkdTask): bool {
        if ($this->taskExists($processData, $taskHash)) {
          return false;
        }

        $processData->data[$task->getStatus()][$taskHash] = $pkdTask;

        return true;
      }
    );

    return $this->safeUpdateProcessData($process, $op);
  }

  public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
  {
    $packedTask = null;

    $op = new UpdateProcessDataClosure($this, function (ProcessData $processData) use (&$packedTask): bool {
      $taskHash = array_key_last($processData->data[CrawlingTask::STATUS_FOR_PROCESSING]);

      if (empty($taskHash)) {
        return false;
      }

      $packedTask = array_pop($processData->data[CrawlingTask::STATUS_FOR_PROCESSING]);

      $processData->data[CrawlingTask::STATUS_IN_PROCESS][$taskHash] = $packedTask;

      return true;
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

    $op = new UpdateProcessDataClosure(
      $this,
      function (ProcessData $processData) use ($task, $taskHash, $fptHashes): bool {
        // Remove tasks those were pushed for processing while crawled.
        foreach ($fptHashes as $fptHash) {
          unset($processData->data[CrawlingTask::STATUS_FOR_PROCESSING][$fptHash]);
        }

        $packedTask = $processData->data[$task->getStatus()][$taskHash];

        unset($processData->data[$task->getStatus()][$taskHash]);

        // Put indexed element to the beginning of the array.
        $processData->data[CrawlingTask::STATUS_FOR_PROCESSING] = [$taskHash => $packedTask] +
          $processData->data[CrawlingTask::STATUS_FOR_PROCESSING];

        return true;
      }
    );

    $update = $this->safeUpdateProcessData($process, $op);

    $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

    return $update;
  }

  private function pushTask(CrawlingProcess $process, CrawlingTask $task, string $status): bool
  {
    $taskHash = $this->genPathHash($task->getNode()->getUri()->getPath());

    $op = new UpdateProcessDataClosure(
      $this,
      function (ProcessData $processData) use ($task, $status, $taskHash): bool {
        $pkdTask = $processData->data[$task->getStatus()][$taskHash];

        $pkdTask['code'] = $task->getNode()->getResponse()?->getCode();

        unset($processData->data[$task->getStatus()][$taskHash]);

        $processData->data[$status][$taskHash] = $pkdTask;

        return true;
      }
    );

    $update = $this->safeUpdateProcessData($process, $op);

    $task->setStatus($status);

    return $update;
  }

  public function createProcess(CrawlingProcess $process): bool
  {
    $filename = $this->getProcessFilename($process);

    return $this->prepareFile($filename);
  }

  public function deleteProcess(CrawlingProcess $process): bool
  {
    $op = new OperateStoreClosure($this, function () use ($process) {
      return $this->deleteProcessData($process);
    });

    return $this->operateStore($op);
  }

  private function safeUpdateProcessData(CrawlingProcess $process, UpdateProcessDataClosure $closure): bool
  {
    $op = new OperateStoreClosure($this, function () use ($process, $closure): bool {
      $processData = $this->readProcessData($process);

      if (!$closure->call($processData)) {
        return false;
      }

      return $this->writeProcessData($process, $processData);
    });

    return $this->operateStore($op);
  }

  private function readProcessData(CrawlingProcess $process): ProcessData
  {
    $content = $this->readProcessContent($process);

    $data = $content ? json_decode($content, true) : $this->defaultProcessData();

    return new ProcessData($data);
  }

  private function writeProcessData(CrawlingProcess $process, ProcessData $processData): bool
  {
    $content = json_encode($processData->data, JSON_PRETTY_PRINT);

    if (false === $content) {
      throw new LogicException('Process data can not be read');
    }

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

    return $this->writeContent($filename, $content);
  }

  private function deleteProcessContent(CrawlingProcess $process): bool
  {
    $filename = $this->getProcessFilename($process);

    return $this->deleteContent($filename);
  }

  private function readContent(string $filename): ?string
  {
    if (!file_exists($filename)) {
      return null;
    }

    $content = file_get_contents($filename);

    if (false === $content) {
      throw new LogicException('Process data can not be read');
    }

    return $content;
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

  private function prepareFile(string $filename): bool
  {
    $fileDir = dirname($filename);

    if (!file_exists($fileDir)) {
      mkdir($fileDir, 0775, true);
    }

    return touch($filename);
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
    return $this->fileStoreDir . '/' . $process->getName() . '.json';
  }

  private function genPathHash(string $path): string
  {
    return hash('sha256', $path);
  }

  /**
   * @return array{'uri': array{'type': string, 'path': string},'type': string, 'code': null|int}
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
   * @param array{'uri': array{'type': string, 'path': string},'type': string} $packedTask
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
        $counts[$status] = count($processData->data[$status]);
      }

      return true;
    });

    $this->operateStore($op);

    return $counts;
  }
}
