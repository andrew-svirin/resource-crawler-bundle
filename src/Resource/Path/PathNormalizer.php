<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;
use RuntimeException;

/**
 * Normalizer for path.
 *
 * @interal
 */
final class PathNormalizer
{
  public function normalize(UriInterface $parentUri, string $childPath): string
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

  private function normalizePathHttp(HttpUri $parentUri, string $childPath): string
  {
    $childParse = parse_url($childPath);

    $childParse['scheme'] ??= '';
    $childParse['path']   ??= '/';

    if (!empty($childParse['host'])) {
      $normalizedScheme = $childParse['scheme'];
      $normalizedHost   = $childParse['host'];
      $normalizedPath   = $childParse['path'];
    } else {
      $parentParse = parse_url($parentUri->getPath());

      $parentParse['scheme'] ??= '';
      $parentParse['path']   ??= '/';

      if (empty($parentParse['host'])) {
        throw new RuntimeException('Parent Host can not be absent.');
      }

      $normalizedScheme = $parentParse['scheme'];
      $normalizedHost   = $parentParse['host'];

      if ($this->isRootPath($childParse['path'])) {
        $normalizedPath = $childParse['path'];
      } else {
        $parentPathDir = rtrim(dirname($parentParse['path']));

        $normalizedPath = $parentPathDir . '/./' . $childParse['path'];
      }
    }

    $normalizedPath = $this->normalizePathAbs($normalizedPath);

    return sprintf(
      '%s//%s%s',
      !empty($normalizedScheme) ? $normalizedScheme . ':' : '',
      $normalizedHost,
      $normalizedPath
    );
  }

  private function isRootPath(string $path): bool
  {
    return str_starts_with($path, '/');
  }

  private function normalizePathAbs(string $path): string
  {
    return '/' . $this->normalizePathRel($path);
  }

  private function normalizePathRel(string $path): string
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

  private function normalizePathFs(FsUri $parentUri, string $path): string
  {
    $normalizedPath = rtrim(dirname($parentUri->getPath())) . '/' . $path;

    return $this->normalizePathAbs($normalizedPath);
  }
}
