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
    public function create(NodeInterface $node): CrawlingTask
    {
        return new CrawlingTask($node);
    }
}
