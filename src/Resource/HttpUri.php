<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * HTTP URI.
 */
class HttpUri implements UriInterface
{
    public function __construct(private readonly string $uri)
    {
    }

    public function getPath(): string
    {
       return $this->uri;
    }
}
