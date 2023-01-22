<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegex;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitution;

/**
 * Factory for resource.
 *
 * @interal
 */
final class ResourceFactory
{
  /**
   * Create Web resource.
   */
  public function createWebResource(
    NodeInterface $node,
    ?PathRegex $pathRegex = null,
    ?PathSubstitution $pathSubstitution = null
  ): WebResource {
    return new WebResource($node, $pathRegex, $pathSubstitution);
  }

  /**
   * Create Disk resource.
   */
  public function createDiskResource(
    NodeInterface $node,
    ?PathRegex $pathRegex = null,
    ?PathSubstitution $pathSubstitution = null
  ): DiskResource {
    return new DiskResource($node, $pathRegex, $pathSubstitution);
  }
}
