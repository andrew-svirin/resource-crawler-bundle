<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * Factory for path.
 *
 * @interal
 */
final class PathFactory
{
  /**
   * Create path.
   */
  public function create(
    string $originalPath,
    ?string $scheme = null,
    ?string $host = null,
    ?string $path = null,
    ?string $query = null,
    ?string $fragment = null
  ): Path {
    return new Path($originalPath, $scheme, $host, $path, $query, $fragment);
  }
}
