<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Entity;

/**
 * Resource based on graph.
 * End-nodes are files.
 */
interface ResourceInterface
{
    /**
     * Get root node.
     */
    public function getRoot(): Node;
}
