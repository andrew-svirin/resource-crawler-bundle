<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;

/**
 * Facade for Process domain.
 *
 * @interal
 */
final class ProcessManager
{
  public function __construct(
    private readonly ProcessStoreInterface $processStore,
    private readonly ProcessFactory $processFactory,
    private readonly TaskFactory $taskFactory
  ) {
  }

  public function loadProcess(Resource $resource): CrawlingProcess
  {
    $process = $this->processFactory->create($resource);
    $node    = $resource->getRoot();

    $this->pushTask($process, $node);

    return $process;
  }

  public function killProcess(Resource $resource): void
  {
    $process = $this->processFactory->create($resource);

    $this->processStore->deleteProcess($process);
  }

  public function popTask(CrawlingProcess $process): ?CrawlingTask
  {
    return $this->processStore->popForProcessingTask($process);
  }

  public function pushTask(CrawlingProcess $process, NodeInterface $node): void
  {
    $task = $this->taskFactory->create($process, $node);

    $this->processStore->pushForProcessingTask($process, $task);
  }

  public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->processStore->pushProcessedTask($process, $task);
  }

  public function ignoreTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->processStore->pushIgnoredTask($process, $task);
  }

  public function errorTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $this->processStore->pushErroredTask($process, $task);
  }

  public function analyzeProcess(CrawlingProcess $process): array
  {
    return $this->processStore->countTasks($process);
  }
}
