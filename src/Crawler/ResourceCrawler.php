<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
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
     * Crawling gathering all urls on the node and collecting them for next crawling iteration.
     * In next iteration will be taken next unique node from the collected stack.
     * As a result task return performed task with link on node that was crawled in iteration.
     *
     * @param string $url Enter URL.
     * @param array|null $pathMasks Mask for path.
     *                              `+<rule>` - to allow, `-<rule>` - to disallow.
     *                              `+site.com/page` - allowing mask
     *                              `-embed` - disallowing mask
     */
    public function crawlHttpResource(string $url, ?array $pathMasks): ?CrawlingTask
    {
        $resource = $this->resourceManager->createWebHtmlResource($url, $pathMasks);

        $process = $this->processManager->loadProcess($resource);

        $task = $this->processManager->popTask($process);

        if (null === $task) {
            return null;
        }

        $this->performTask($task);

        $this->processManager->destroyTask($process, $task);

        return $task;
    }

    private function performTask(CrawlingTask $task): void
    {
        $node = $task->getNode();

        $this->nodeCrawler->crawl($task, $node);
    }

    /**
     * Reset crawling relating data.
     * @param string $url Enter URL.
     */
    public function resetHttpResource(string $url): void
    {
        $resource = $this->resourceManager->createWebHtmlResource($url);

        $this->processManager->killProcess($resource);
    }
}
