<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;

/**
 * Validator for path.
 *
 * @interal
 */
final class PathValidator
{
  public function __construct(private readonly PathExtractor $pathExtractor)
  {
  }

  public function isValid(UriInterface $parentUri, Path $childPath): bool
  {
    if ($parentUri instanceof HttpUri) {
      $isValid = $this->isValidPathHttp($childPath);
    } elseif ($parentUri instanceof FsUri) {
      $isValid = $this->isValidPathFs($childPath);
    } else {
      throw new LogicException('Incorrect uri.');
    }

    return $isValid;
  }

  private function isValidPathHttp(Path $path): bool
  {
    if ($this->hasDisallowedCharacters($path->getOriginalPath())) {
      return false;
    }

    if ($this->hasDisallowedScheme($path->getOriginalPath())) {
      return false;
    }

    return true;
  }

  private function isValidPathFs(Path $path): bool
  {
    if ($this->hasDisallowedCharacters($path->getOriginalPath())) {
      return false;
    }

    if ($this->hasDisallowedScheme($path->getOriginalPath())) {
      return false;
    }

    if ($path->isRoot() || $path->isAbsolute()) {
      return false;
    }

    return true;
  }

  private function hasDisallowedCharacters(string $path): bool
  {
    $pattern = '/^[\p{L}\d\/\'\-\(\)\[\]"`~#!?$&@%{}*+<>=.,;:_ ]*$/u';

    return 0 === preg_match($pattern, $path);
  }

  private function hasDisallowedScheme(string $path): bool
  {
    $scheme = $this->pathExtractor->extractScheme($path);

    return !empty($scheme) && !in_array($scheme, PathInterface::ALL_SCHEMES);
  }
}
