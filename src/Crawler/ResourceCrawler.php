<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;
use AndrewSvirin\ResourceCrawlerBundle\Process\Analyze\CrawlingAnalyze;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;

/**
 * Crawler for resource.
 */
final class ResourceCrawler
{
  public function __construct(
    private readonly ResourceManager $resourceManager,
    private readonly ProcessManager $processManager,
    private readonly NodeCrawler $nodeCrawler
  ) {
  }

  /**
   * @param string[]|null $pathMasks
   * @param array<array<string>>|null $substRules Substitution rules.
   */
  public function crawlWebResource(
    string $url,
    ?array $pathMasks = null,
    ?array $substRules = null,
    RefHandlerClosureInterface ...$refHandlers
  ): ?CrawlingTask {
    $resource = $this->resourceManager->createWebResource($url, $pathMasks, $substRules);

    return $this->crawlResource($resource, ...$refHandlers);
  }

  /**
   * @param string[]|null $pathMasks
   * @param array<array<string>>|null $substRules Substitution rules.
   */
  public function crawlDiskResource(
    string $path,
    ?array $pathMasks = null,
    ?array $substRules = null,
    RefHandlerClosureInterface ...$refHandlers
  ): ?CrawlingTask {
    $resource = $this->resourceManager->createDiskResource($path, $pathMasks, $substRules);

    return $this->crawlResource($resource, ...$refHandlers);
  }

  /**
   * Crawling gathering all urls on the node and collecting them for next crawling iteration.
   * In next iteration will be taken next unique node from the collected stack.
   * As a result task return performed task with link on node that was crawled in iteration.
   */
  private function crawlResource(Resource $resource, RefHandlerClosureInterface ...$refHandlers): ?CrawlingTask
  {
    $process = $this->processManager->loadProcess($resource);

    $task = $this->processManager->popTask($process);

    if (null === $task) {
      return null;
    }

    $substitutePathOp = new CrawlRefHandlerClosure($this, function (RefPath $refPath, CrawlingTask $task) {
      if (!$refPath->isValid()) {
        return;
      }

      $refPath->setNormalizedPath(
        $this->resourceManager->substitutePath(
          $task->getProcess()->getResource(),
          $refPath->getNormalizedPath()
        )
      );
    });

    $pushTasksOp = new CrawlRefHandlerClosure($this, function (RefPath $refPath, CrawlingTask $task) {
      if (!$refPath->isValid()) {
        return;
      }

      $newNode = $this->nodeCrawler->createRefNode(
        $refPath->getRef(),
        $task->getProcess()->getResource(),
        $refPath->getNormalizedPath()
      );

      if ($this->processManager->pushTask($task->getProcess(), $newNode)) {
        $task->appendPushedForProcessingPath($newNode->getUri()->getPath());
      }
    });

    array_unshift($refHandlers, $substitutePathOp, $pushTasksOp);

    $this->tryPerformTask($task, ...$refHandlers);

    return $task;
  }

  private function tryPerformTask(CrawlingTask $task, RefHandlerClosureInterface ...$refHandlers): void
  {
    $isTaskPerformable = $this->resourceManager->isPerformablePath(
      $task->getNode()->getUri()->getPath(),
      $task->getProcess()->getResource()->pathRegex()
    );

    if ($isTaskPerformable) {
      $this->nodeCrawler->crawl($task->getNode());

      $this->walkTaskNode($task, ...$refHandlers);

      if ($this->resourceManager->isNotSuccessNode($task->getNode())) {
        $this->processManager->errorTask($task->getProcess(), $task);
      } else {
        $this->processManager->destroyTask($task->getProcess(), $task);
      }
    } else {
      $this->processManager->ignoreTask($task->getProcess(), $task);
    }
  }

  public function resetWebResource(string $url): bool
  {
    $resource = $this->resourceManager->createWebResource($url);

    return $this->resetResource($resource);
  }

  public function resetDiskResource(string $path): bool
  {
    $resource = $this->resourceManager->createDiskResource($path);

    return $this->resetResource($resource);
  }

  /**
   * Rollback task.
   * Revert task back for crawling.
   */
  public function rollbackTask(CrawlingTask $task): bool
  {
    return $this->processManager->revertTask($task->getProcess(), $task);
  }

  /**
   * Reset crawling relating data.
   */
  private function resetResource(Resource $resource): bool
  {
    return $this->processManager->killProcess($resource);
  }

  /**
   * Analyze web resource crawling process.
   */
  public function analyzeCrawlingWebResource(string $url): CrawlingAnalyze
  {
    $resource = $this->resourceManager->createWebResource($url);

    return $this->analyzeCrawlingResource($resource);
  }

  /**
   * Analyze disk resource crawling process.
   */
  public function analyzeCrawlingDiskResource(string $path): CrawlingAnalyze
  {
    $resource = $this->resourceManager->createDiskResource($path);

    return $this->analyzeCrawlingResource($resource);
  }

  private function analyzeCrawlingResource(Resource $resource): CrawlingAnalyze
  {
    $process = $this->processManager->loadProcess($resource);

    return $this->processManager->analyze($process);
  }

  /**
   * Walk task node elements to handle them.
   * Possible to modify task node content.
   */
  private function walkTaskNode(CrawlingTask $task, RefHandlerClosureInterface ...$refHandlers): void
  {
    foreach ($this->nodeCrawler->walkNode($task->getNode()) as $refPath) {
      if ($refPath->isValid()) {
        $refPath->setPerformable($this->resourceManager->isPerformablePath(
          $refPath->getNormalizedPath(),
          $task->getProcess()->getResource()->pathRegex()
        ));
      }

      foreach ($refHandlers as $refHandler) {
        $refHandler->call($refPath, $task);
      }
    }
  }
}
