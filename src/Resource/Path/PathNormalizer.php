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
      $nmdScheme = $childPath->getScheme();
      $nmdHost   = $childPath->getHost();
      $nmdPath   = $childPath->getPath();
    } else {
      $parentPath = $this->pathComposer->decompose($parentUri->getPath());

      $nmdScheme = $parentPath->getScheme();
      $nmdHost   = $parentPath->getHost();

      if ($childPath->isRoot()) {
        $nmdPath = $childPath->getPath();
      } else {
        $parentPathDir = rtrim(dirname($parentPath->getPath()));

        $nmdPath = $parentPathDir . '/./' . $childPath->getPath();
      }
    }

    $nmdQuery = $childPath->getQuery();

    $nmdFragment = $childPath->getFragment();

    $nmdPath = $this->normalizePathAbsolute($nmdPath);

    return $this->pathComposer->compose($nmdScheme, $nmdHost, $nmdPath, $nmdQuery, $nmdFragment);
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
