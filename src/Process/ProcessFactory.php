<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Factory for the process.
 *
 * @interal
 */
final class ProcessFactory
{
    public function create(): CrawlingProcess
    {
        return new CrawlingProcess();
    }
}
