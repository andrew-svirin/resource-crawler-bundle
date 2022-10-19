<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Resource node.
 */
abstract class Node
{
    public function __construct(private readonly UriInterface $uri)
    {
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
