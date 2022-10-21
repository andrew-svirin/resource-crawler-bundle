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
        private readonly NodeFactory $nodeFactory,
        private readonly UriFactory $uriFactory
    ) {
    }

    public function createHttpResource(string $url): HttpResource
    {
        $uri = $this->uriFactory->createHttp($url);
        $node = $this->nodeFactory->createHtml($uri);

        return $this->resourceFactory->createHttp($node);
    }

    public function readUri(UriInterface $uri): string
    {
        return $this->reader->read($uri);
    }
}
