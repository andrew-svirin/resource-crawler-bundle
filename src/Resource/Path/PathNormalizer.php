<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;

/**
 * Normalizer for path.
 *
 * @interal
 */
final class PathNormalizer
{
  public function __construct(private readonly PathComposer $pathComposer)
  {
  }

  public function normalize(UriInterface $parentUri, Path $childPath): string
  {
    if ($parentUri instanceof HttpUri) {
      $path = $this->normalizePathHttp($parentUri, $childPath);
    } elseif ($parentUri instanceof FsUri) {
      $path = $this->normalizePathFs($parentUri, $childPath);
    } else {
      throw new LogicException('Incorrect uri.');
    }

    return $path;
  }

  private function normalizePathHttp(HttpUri $parentUri, Path $childPath): string
  {
    if (PathInterface::SCHEME_DATA === $childPath->getScheme()) {
      return $childPath->getOriginalPath();
    }

    if ($childPath->isAbsolute()) {
      $normalizedScheme = $childPath->getScheme();
      $normalizedHost   = $childPath->getHost();
      $normalizedPath   = $childPath->getPath();
    } else {
      $parentPath = $this->pathComposer->decompose($parentUri->getPath());

      $normalizedScheme = $parentPath->getScheme();
      $normalizedHost   = $parentPath->getHost();

      if ($childPath->isRoot()) {
        $normalizedPath = $childPath->getPath();
      } else {
        $parentPathDir = rtrim(dirname($parentPath->getPath()));

        $normalizedPath = $parentPathDir . '/./' . $childPath->getPath();
      }
    }

    $normalizedQuery = $childPath->getQuery();

    $normalizedPath = $this->normalizePathAbsolute($normalizedPath);

    return $this->pathComposer->compose($normalizedScheme, $normalizedHost, $normalizedPath, $normalizedQuery);
  }

  private function normalizePathAbsolute(string $path): string
  {
    return '/' . $this->normalizePathRelative($path);
  }

  private function normalizePathRelative(string $path): string
  {
    $explode = explode('/', $path);

    $pathSegments = [];

    foreach ($explode as $segment) {
      if (($segment == '.') || empty($segment)) {
        continue;
      }
      if ($segment == '..') {
        array_pop($pathSegments);
      } else {
        $pathSegments[] = $segment;
      }
    }

    return implode('/', $pathSegments);
  }

  private function normalizePathFs(FsUri $parentUri, Path $path): string
  {
    $normalizedPath = rtrim(dirname($parentUri->getPath())) . '/' . $path->getOriginalPath();

    return $this->normalizePathAbsolute($normalizedPath);
  }
}
