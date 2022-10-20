<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Model implements crawling process.
 *
 * @interal
 */
final class ProcessManager
{
    public function __construct(
        private readonly ProcessFactory $processFactory,
        private readonly ProcessSaverInterface $processSaver
    ) {
    }

    public function load(HttpResource $resource): CrawlingProcess
    {
        return new CrawlingProcess();
    }

    public function popTask(CrawlingProcess $process): CrawlingTask
    {

    }

    public function pushTask(Node $node): void
    {

    }

    public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
    {

    }
}
