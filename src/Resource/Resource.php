<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;

/**
 * Filesystem resource.
 *
 * @interal
 */
abstract class Resource implements ResourceInterface
{
    public function __construct(private readonly NodeInterface $node, private readonly string $pathRegex)
    {
    }

    public function getRoot(): NodeInterface
    {
        return $this->node;
    }

    public function pathRegex(): string
    {
        return $this->pathRegex;
    }
}
