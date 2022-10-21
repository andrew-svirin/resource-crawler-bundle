<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Reader\ResourceReader;
use LogicException;

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

    private function createHttpHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createHtml($uri);
    }

    private function createHttpImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createImg($uri);
    }

    private function createFsHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createHtml($uri);
    }

    private function createFsImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createImg($uri);
    }

    public function createHtmlNode(ResourceInterface $resource, mixed $path): NodeInterface
    {
        if ($resource instanceof HttpResource) {
            $node = $this->createHttpHtmlNode($path);
        } elseif ($resource instanceof FsResource) {
            $node = $this->createFsHtmlNode($path);
        } else {
            throw new LogicException('Resource is incorrect.');
        }

        return $node;
    }

    public function createImgNode(ResourceInterface $resource, string $path): NodeInterface
    {
        if ($resource instanceof HttpResource) {
            $node = $this->createHttpImgNode($path);
        } elseif ($resource instanceof FsResource) {
            $node = $this->createFsImgNode($path);
        } else {
            throw new LogicException('Resource is incorrect.');
        }

        return $node;
    }
}
