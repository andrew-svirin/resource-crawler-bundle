<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;

/**
 * Factory for the task.
 *
 * @interal
 */
final class TaskFactory
{
    public function create(CrawlingProcess $process, NodeInterface $node): CrawlingTask
    {
        return new CrawlingTask($process, $node);
    }
}
