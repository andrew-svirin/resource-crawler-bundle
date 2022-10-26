<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Uri;

/**
 * URI.
 *
 * @interal
 */
abstract class Uri implements UriInterface
{
    public function __construct(private readonly string $path)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
