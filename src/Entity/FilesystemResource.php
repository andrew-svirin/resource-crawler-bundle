<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Entity;

/**
 * Filesystem resource.
 */
class FilesystemResource implements ResourceInterface
{
    public function __construct(private $dir)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): Node
    {
        return $this->dir;
    }
}
