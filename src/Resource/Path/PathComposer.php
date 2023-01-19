<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * Composer for path.
 *
 * @interal
 */
final class PathComposer
{
  public function __construct(private readonly PathFactory $pathFactory)
  {
  }

  public function compose(
    string $scheme,
    string $host,
    string $path,
    string $query = null,
    string $fragment = null
  ): string {
    return sprintf(
      '%s://%s%s%s%s',
      $scheme,
      $host,
      $path,
      $query ? '?' . $query : '',
      $fragment ? '#' . $fragment : ''
    );
  }

  public function decompose(string $originalPath): Path
  {
    $parse = parse_url($originalPath);

    return $this->pathFactory->create(
      $originalPath,
      $parse['scheme'] ?? PathInterface::SCHEME_HTTPS,
      $parse['host'] ?? null,
      $parse['path'] ?? '/',
      $parse['query'] ?? null,
      $parse['fragment'] ?? null
    );
  }
}
