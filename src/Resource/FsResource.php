<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Filesystem resource.
 *
 * @interal
 */
class FsResource implements ResourceInterface
{
    public function __construct(private $uri)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): Node
    {
        return $this->uri;
    }
}
