<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Path;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathComposer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegex;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitution;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutionCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutor;
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
    private readonly PathExtractor $pathExtractor,
    private readonly PathRegexCreator $pathRegexCreator,
    private readonly PathSubstitutionCreator $pathSubstitutionCreator,
    private readonly PathSubstitutor $pathSubstitutor
  ) {
  }

  public function readUri(UriInterface $uri): Response
  {
    return $this->reader->read($uri);
  }

  /**
   * @param string[]|null $pathMasks
   * @param array<array<string>>|null $substRules
   */
  public function createWebResource(string $path, ?array $pathMasks = null, ?array $substRules = null): WebResource
  {
    $node             = $this->createWebHtmlNode($path);
    $pathRegex        = $this->resolvePathRegex($pathMasks);
    $pathSubstitution = $this->resolvePathSubstitution($substRules);

    return $this->resourceFactory->createWebResource($node, $pathRegex, $pathSubstitution);
  }

  /**
   * @param string[]|null $pathMasks
   * @param array<array<string>>|null $substRules
   */
  public function createDiskResource(string $path, ?array $pathMasks = null, ?array $substRules = null): DiskResource
  {
    $node             = $this->createDiskHtmlNode($path);
    $pathRegex        = $this->resolvePathRegex($pathMasks);
    $pathSubstitution = $this->resolvePathSubstitution($substRules);

    return $this->resourceFactory->createDiskResource($node, $pathRegex, $pathSubstitution);
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

  public function isPerformablePath(string $path, ?PathRegex $pathRegex = null): bool
  {
    if (null === $pathRegex) {
      return true;
    }

    $scheme = $this->pathExtractor->extractScheme($path);

    return PathInterface::SCHEME_DATA === $scheme || $this->pathRegexMatcher->isMatching($pathRegex, $path);
  }

  public function isNotSuccessNode(NodeInterface $node): bool
  {
    return $node->getResponse()->getCode() >= 400;
  }

  public function isNotHtmlNode(NodeInterface $node): bool
  {
    if (str_contains(($node->getResponse()->getHeaders()['content-type'][0] ?? ''), 'html')) {
      return false;
    }

    if (($node->getResponse()->getHeaders()['content-length'][0] ?? 0) > 1000000) {
      return true;
    }

    if (!str_contains(strtolower(substr($node->getResponse()->getContent(), 0, 200)), 'html')) {
      return true;
    }

    return false;
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

  /**
   * @param string[]|null $pathMasks
   */
  private function resolvePathRegex(?array $pathMasks = null): ?PathRegex
  {
    if (null === $pathMasks) {
      return null;
    }

    return $this->pathRegexCreator->create($pathMasks);
  }

  /**
   * @param array<array<string>>|null $substRules
   */
  private function resolvePathSubstitution(?array $substRules = null): ?PathSubstitution
  {
    if (null === $substRules) {
      return null;
    }

    return $this->pathSubstitutionCreator->create($substRules);
  }

  public function substitutePath(ResourceInterface $resource, string $path): string
  {
    if (empty($resource->pathSubstitution())) {
      return $path;
    }

    return $this->pathSubstitutor->substitute($resource->pathSubstitution(), $path);
  }
}
