<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Filesystem resource.
 *
 * @interal
 */
abstract class Resource implements ResourceInterface
{
    public function __construct(private readonly NodeInterface $node)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): NodeInterface
    {
        return $this->node;
    }
}
