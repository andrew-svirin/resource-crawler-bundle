<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

/**
 * Factory for the process.
 *
 * @interal
 */
final class ProcessFactory
{
    public function create(string $id): CrawlingProcess
    {
        return new CrawlingProcess($id);
    }
}
