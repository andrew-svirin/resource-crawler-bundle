<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;

/**
 * Resource based on graph.
 * End-nodes are files.
 */
interface ResourceInterface
{
    /**
     * Get root node.
     */
    public function getRoot(): NodeInterface;

    /**
     * Path regex for crawling.
     */
    public function pathRegex(): string;
}
