<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegex;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexCreator;

/**
 * Factory for resource.
 *
 * @interal
 */
final class ResourceFactory
{
  public function __construct(
    private readonly PathRegexCreator $pathRegexCreator
  ) {
  }

  /**
   * Create Web resource.
   * @param string[]|null $pathMasks
   */
  public function createWeb(NodeInterface $node, ?array $pathMasks = null): WebResource
  {
    $pathRegex = $this->resolvePathRegex($pathMasks);

    return new WebResource($node, $pathRegex);
  }

  /**
   * Create Disk resource.
   * @param string[]|null $pathMasks
   */
  public function createDisk(NodeInterface $node, ?array $pathMasks = null): DiskResource
  {
    $pathRegex = $this->resolvePathRegex($pathMasks);

    return new DiskResource($node, $pathRegex);
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
}
