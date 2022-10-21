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

    public function createHttpHtmlResource(string $path): HttpResource
    {
        $node = $this->createHttpHtmlNode($path);

        return $this->resourceFactory->createHttp($node);
    }

    public function readUri(UriInterface $uri): string
    {
        return $this->reader->read($uri);
    }

    public function createHttpHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createHtml($uri);
    }

    public function createHttpImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createImg($uri);
    }

    public function createFsHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createHtml($uri);
    }

    public function createFsImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createImg($uri);
    }
}
