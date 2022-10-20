<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

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
}
