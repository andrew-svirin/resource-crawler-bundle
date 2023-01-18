<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Analyze\AnalyzeFactory;
use AndrewSvirin\ResourceCrawlerBundle\Process\Analyze\CrawlingAnalyze;
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
    private readonly TaskFactory $taskFactory,
    private readonly AnalyzeFactory $analyzeFactory,
  ) {
  }

  public function loadProcess(Resource $resource): CrawlingProcess
  {
    $process = $this->processFactory->create($resource);
    $node    = $resource->getRoot();

    $this->pushTask($process, $node);

    return $process;
  }

  public function killProcess(Resource $resource): bool
  {
    $process = $this->processFactory->create($resource);

    return $this->processStore->deleteProcess($process);
  }

  public function popTask(CrawlingProcess $process): ?CrawlingTask
  {
    return $this->processStore->popForProcessingTask($process);
  }

  public function pushTask(CrawlingProcess $process, NodeInterface $node): bool
  {
    $task = $this->taskFactory->create($process, $node);

    return $this->processStore->pushForProcessingTask($process, $task);
  }

  public function destroyTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->processStore->pushProcessedTask($process, $task);
  }

  public function ignoreTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->processStore->pushIgnoredTask($process, $task);
  }

  public function errorTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->processStore->pushErroredTask($process, $task);
  }

  public function analyze(CrawlingProcess $process): CrawlingAnalyze
  {
    $counts = $this->processStore->countTasks($process);

    return $this->analyzeFactory->create($counts);
  }
}
