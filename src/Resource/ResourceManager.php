<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;

/**
 * Manager for Resource domain.
 *
 * @interal
 */
final class ResourceManager
{
    public function __construct(
        private readonly ResourceReader $reader,
        private readonly ResourceFactory $resourceFactory,
        private readonly NodeFactory $nodeFactory,
        private readonly UriFactory $uriFactory,
        private readonly PathRegexMatcher $pathRegexMatcher,
    ) {
    }

    public function createWebHtmlResource(string $path, ?array $pathMasks): WebResource
    {
        $node = $this->createWebHtmlNode($path);

        return $this->resourceFactory->createWeb($node, $pathMasks);
    }

    public function readUri(UriInterface $uri): string
    {
        return $this->reader->read($uri);
    }

    private function createWebHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createHtml($uri);
    }

    private function createWebImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createHttp($path);

        return $this->nodeFactory->createImg($uri);
    }

    private function createDiskHtmlNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createHtml($uri);
    }

    private function createDiskImgNode(string $path): NodeInterface
    {
        $uri = $this->uriFactory->createFs($path);

        return $this->nodeFactory->createImg($uri);
    }

    public function createHtmlNode(ResourceInterface $resource, mixed $path): NodeInterface
    {
        if ($resource instanceof WebResource) {
            $node = $this->createWebHtmlNode($path);
        } elseif ($resource instanceof DiskResource) {
            $node = $this->createDiskHtmlNode($path);
        } else {
            throw new LogicException('Resource is incorrect.');
        }

        return $node;
    }

    public function createImgNode(ResourceInterface $resource, string $path): NodeInterface
    {
        if ($resource instanceof WebResource) {
            $node = $this->createWebImgNode($path);
        } elseif ($resource instanceof DiskResource) {
            $node = $this->createDiskImgNode($path);
        } else {
            throw new LogicException('Resource is incorrect.');
        }

        return $node;
    }

    public function isMatchingPathRegex(string $pathRegex, string $path): bool
    {
        return $this->pathRegexMatcher->isMatching($pathRegex, $path);
    }
}
