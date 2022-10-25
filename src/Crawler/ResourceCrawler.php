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
     * @param string $url Enter URL.
     * @param array|null $pathMasks Mask for path.
     *                              `+<rule>` - to allow, `-<rule>` - to disallow.
     *                              `+site.com/page` - allowing mask
     *                              `-embed` - disallowing mask
     * @return void
     */
    public function crawlHttpResource(string $url, ?array $pathMasks): void
    {
        $resource = $this->resourceManager->createWebHtmlResource($url, $pathMasks);

        $process = $this->processManager->load($resource);

        $task = $this->processManager->popTask($process);

        $this->performTask($task);

        $this->processManager->destroyTask($process, $task);
    }

    private function performTask(CrawlingTask $task): void
    {
        $node = $task->getNode();

        $this->nodeCrawler->crawl($task, $node);
    }
}
