<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Process\Analyze\CrawlingAnalyze;
use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;
use DOMElement;

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
   */
  public function crawlWebResource(string $url, ?array $pathMasks = null): ?CrawlingTask
  {
    $resource = $this->resourceManager->createWebHtmlResource($url, $pathMasks);

    return $this->crawlResource($resource);
  }

  /**
   * @param string[]|null $pathMasks
   */
  public function crawlDiskResource(string $path, ?array $pathMasks = null): ?CrawlingTask
  {
    $resource = $this->resourceManager->createDiskFsResource($path, $pathMasks);

    return $this->crawlResource($resource);
  }

  /**
   * Crawling gathering all urls on the node and collecting them for next crawling iteration.
   * In next iteration will be taken next unique node from the collected stack.
   * As a result task return performed task with link on node that was crawled in iteration.
   */
  private function crawlResource(Resource $resource): ?CrawlingTask
  {
    $process = $this->processManager->loadProcess($resource);

    $task = $this->processManager->popTask($process);

    if (null === $task) {
      return null;
    }

    $this->tryPerformTask($process, $task);

    return $task;
  }

  private function tryPerformTask(CrawlingProcess $process, CrawlingTask $task): void
  {
    $isTaskPerformable = $this->resourceManager->isPerformablePath(
      $task->getNode()->getUri()->getPath(),
      $task->getProcess()->getResource()->pathRegex()
    );

    if ($isTaskPerformable) {
      $this->performTask($task);

      if ($this->resourceManager->isNotSuccessNode($task->getNode())) {
        $this->processManager->errorTask($process, $task);
      } else {
        $this->processManager->destroyTask($process, $task);
      }
    } else {
      $this->processManager->ignoreTask($process, $task);
    }
  }

  private function performTask(CrawlingTask $task): void
  {
    $this->nodeCrawler->crawl($task->getNode());

    $op = new CrawlRefHandlerClosure(
      $this,
      function (DOMElement $ref, bool $isValidPath, ?string $normalizedPath) use ($task) {
        if (!$isValidPath) {
          return;
        }

        $newNode = $this->nodeCrawler->createRefNode($ref, $task->getProcess()->getResource(), $normalizedPath);

        if ($this->processManager->pushTask($task->getProcess(), $newNode)) {
          $task->appendPushedForProcessingPath($newNode->getUri()->getPath());
        }
      }
    );

    $this->walkTaskNode($task, $op);
  }

  public function resetWebResource(string $url): void
  {
    $resource = $this->resourceManager->createWebHtmlResource($url);

    $this->resetResource($resource);
  }

  public function resetDiskResource(string $path): void
  {
    $resource = $this->resourceManager->createDiskFsResource($path);

    $this->resetResource($resource);
  }

  /**
   * Rollback task.
   * Revert task back for crawling.
   */
  public function rollbackTask(CrawlingTask $task): void
  {
    $this->processManager->revertTask($task->getProcess(), $task);
  }

  /**
   * Reset crawling relating data.
   */
  private function resetResource(Resource $resource): void
  {
    $this->processManager->killProcess($resource);
  }

  /**
   * Analyze web resource crawling process.
   */
  public function analyzeCrawlingWebResource(string $url): CrawlingAnalyze
  {
    $resource = $this->resourceManager->createWebHtmlResource($url);

    return $this->analyzeCrawlingResource($resource);
  }

  /**
   * Analyze disk resource crawling process.
   */
  public function analyzeCrawlingDiskResource(string $path): CrawlingAnalyze
  {
    $resource = $this->resourceManager->createDiskFsResource($path);

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
  public function walkTaskNode(CrawlingTask $task, RefHandlerClosureInterface $refHandler): void
  {
    foreach ($this->nodeCrawler->walkNode($task->getNode()) as $args) {
      $ref            = &$args[0];
      $isValidPath    = &$args[1];
      $normalizedPath = &$args[2];

      $isPerformablePath = $isValidPath ? $this->resourceManager->isPerformablePath(
        $normalizedPath,
        $task->getProcess()->getResource()->pathRegex()
      ) : null;

      $refHandler->call($ref, $isValidPath, $normalizedPath, $isPerformablePath);
    }
  }
}
