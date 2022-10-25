<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Uri;

/**
 * HTTP URI.
 *
 * @interal
 */
final class HttpUri implements UriInterface
{
    public function __construct(private readonly string $uri)
    {
    }

    public function getPath(): string
    {
        return $this->uri;
    }
}
