<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegex;

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
    public function pathRegex(): ?PathRegex;
}
