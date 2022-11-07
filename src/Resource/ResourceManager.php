<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Path;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathComposer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegex;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Response\Response;
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
    private readonly PathComposer $pathComposer,
    private readonly PathValidator $pathValidator,
    private readonly PathNormalizer $pathNormalizer,
    private readonly PathExtractor $pathExtractor
  ) {
  }

  public function readUri(UriInterface $uri): Response
  {
    return $this->reader->read($uri);
  }

  /**
   * @param string[]|null $pathMasks
   */
  public function createWebHtmlResource(string $path, ?array $pathMasks = null): WebResource
  {
    $node = $this->createWebHtmlNode($path);

    return $this->resourceFactory->createWeb($node, $pathMasks);
  }

  /**
   * @param string[]|null $pathMasks
   */
  public function createDiskFsResource(string $path, ?array $pathMasks = null): DiskResource
  {
    $node = $this->createDiskHtmlNode($path);

    return $this->resourceFactory->createDisk($node, $pathMasks);
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

  public function createHtmlNode(ResourceInterface $resource, string $path): NodeInterface
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

  public function isPerformablePath(string $path, PathRegex $pathRegex): bool
  {
    $scheme = $this->pathExtractor->extractScheme($path);

    return PathInterface::SCHEME_DATA === $scheme || $this->pathRegexMatcher->isMatching($pathRegex, $path);
  }

  public function isNotSuccessNode(NodeInterface $node): bool
  {
    return $node->getResponse()->getCode() >= 400;
  }

  public function decomposePath(string $path): Path
  {
    return $this->pathComposer->decompose($path);
  }

  public function isValidPath(UriInterface $parentUri, Path $path): bool
  {
    return $this->pathValidator->isValid($parentUri, $path);
  }

  public function normalizePath(UriInterface $parentUri, Path $path): string
  {
    return $this->pathNormalizer->normalize($parentUri, $path);
  }
}
