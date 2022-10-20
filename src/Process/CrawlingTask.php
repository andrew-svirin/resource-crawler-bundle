<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Model implements crawling task.
 *
 * @interal
 */
final class CrawlingTask
{
    public function __construct(private readonly Node $node)
    {
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
