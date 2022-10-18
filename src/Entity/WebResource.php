<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Entity;

/**
 * Web resource.
 */
class WebResource implements ResourceInterface
{
    public function __construct(private $url)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): Node
    {
        return $this->url;
    }
}
