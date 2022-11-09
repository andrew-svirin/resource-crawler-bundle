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

  public function taskExists(CrawlingProcess $process, CrawlingTask $task): bool
  {
    $processData = $this->readProcessData($process);

    $taskHash = $this->genTaskHash($task);

    foreach (CrawlingTask::ALL_STATUSES as $status) {
      if (in_array($taskHash, array_keys($processData[$status]))) {
        return true;
      }
    }

    return false;
  }

  public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $taskHash   = $this->genTaskHash($task);
    $packedTask = $this->packTask($task);

    $this->updateProcessData($process, function (array $processData) use ($taskHash, $packedTask) {
      $processData[CrawlingTask::STATUS_FOR_PROCESSING][$taskHash] = $packedTask;

      return $processData;
    });
  }

  public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
  {
    $packedTask = null;

    $this->updateProcessData($process, function (array $processData) use (&$packedTask) {
      $taskHash = array_key_last($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

      if (empty($taskHash)) {
        return $processData;
      }

      $packedTask = array_pop($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

      $processData[CrawlingTask::STATUS_IN_PROCESS][$taskHash] = $packedTask;

      return $processData;
    });

    if (null === $packedTask) {
      return null;
    }

    $task = $this->unpackTask($process, $packedTask);

    $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

    return $task;
  }

  public function popInProcessTask(CrawlingProcess $process): ?CrawlingTask
  {
    $processData = $this->readProcessData($process);

    $packedTask = array_pop($processData[CrawlingTask::STATUS_IN_PROCESS]);

    if (empty($packedTask)) {
      return null;
    }

    $task = $this->unpackTask($process, $packedTask);

    $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

    return $task;
  }

  public function pushProcessedTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->pushTask($process, $task, CrawlingTask::STATUS_PROCESSED);
  }

  public function pushIgnoredTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->pushTask($process, $task, CrawlingTask::STATUS_IGNORED);
  }

  public function pushErroredTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->pushTask($process, $task, CrawlingTask::STATUS_ERRORED);
  }

  private function pushTask(CrawlingProcess $process, CrawlingTask $task, string $status): void
  {
    $taskHash = $this->genTaskHash($task);

    $this->updateProcessData($process, function (array $processData) use ($task, $status, $taskHash) {

      $packedTask = $processData[$task->getStatus()][$taskHash];

      $packedTask['code'] = $task->getNode()->getResponse()?->getCode();

      unset($processData[$task->getStatus()][$taskHash]);

      $processData[$status][$taskHash] = $packedTask;

      return $processData;
    });

    $task->setStatus($status);
  }

  private function updateProcessData(CrawlingProcess $process, callable $closure): void
  {
    $this->operateStore(function () use ($process, $closure) {
      $processData = $this->readProcessData($process);

      $processData = call_user_func_array($closure, [$processData]);

      $this->writeProcessData($process, $processData);
    });
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
  private function writeProcessData(CrawlingProcess $process, array $processData): void
  {
    $content = json_encode($processData, JSON_PRETTY_PRINT);

    $this->writeProcessContent($process, $content);
  }

  private function readProcessContent(CrawlingProcess $process): ?string
  {
    $filename = $this->getProcessFilename($process);

    return $this->readContent($filename);
  }

  private function writeProcessContent(CrawlingProcess $process, string $content): void
  {
    $filename = $this->getProcessFilename($process);

    $this->prepareFile($filename);

    $this->writeContent($filename, $content);
  }

  private function deleteProcessContent(CrawlingProcess $process): void
  {
    $filename = $this->getProcessFilename($process);

    $this->deleteContent($filename);
  }

  private function readContent(string $filename): ?string
  {
    return file_exists($filename) ? file_get_contents($filename) : null;
  }

  private function writeContent(string $filename, string $content): void
  {
    if (file_exists($filename)) {
      file_put_contents($filename, $content);
    }
  }

  private function deleteContent(string $filename): void
  {
    if (file_exists($filename)) {
      unlink($filename);
    }
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

  private function genTaskHash(CrawlingTask $task): string
  {
    return hash('sha256', $task->getNode()->getUri()->getPath());
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

  public function deleteProcess(CrawlingProcess $process): void
  {
    $this->deleteProcessContent($process);
  }
}
