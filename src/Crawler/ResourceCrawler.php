<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
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

        $this->tryPerformTask($resource, $process, $task);

        return $task;
    }

    private function tryPerformTask(Resource $resource, CrawlingProcess $process, CrawlingTask $task): void
    {
        $isTaskPerformable = $this->resourceManager->isMatchingPathRegex(
            $resource->pathRegex(),
            $task->getNode()->getUri()->getPath()
        );

        if ($isTaskPerformable) {
            $this->performTask($task);

            $this->processManager->destroyTask($process, $task);
        } else {
            $this->processManager->ignoreTask($process, $task);
        }
    }

    private function performTask(CrawlingTask $task): void
    {
        $this->nodeCrawler->crawl($task);
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
     * Reset crawling relating data.
     */
    private function resetResource(Resource $resource): void
    {
        $this->processManager->killProcess($resource);
    }
}
