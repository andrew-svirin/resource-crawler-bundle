<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;

/**
 * Model implements crawling task.
 *
 * @interal
 */
final class CrawlingTask
{
    public function __construct(private readonly NodeInterface $node)
    {
    }

    public function getNode(): NodeInterface
    {
        return $this->node;
    }
}
