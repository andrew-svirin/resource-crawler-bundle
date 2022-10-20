<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Reader\ResourceReader;

/**
 * Manager for resources.
 *
 * @interal
 */
final class ResourceManager
{
    public function __construct(
        private readonly ResourceReader $reader,
        private readonly ResourceFactory $resourceFactory,
    ) {
    }

    public function createHttpResource(string $url): HttpResource
    {
        return $this->resourceFactory->createHttp($url);
    }

    public function readUri(UriInterface $uri): string
    {
        return $this->reader->read($uri);
    }
}
