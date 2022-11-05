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
  public function isValid(UriInterface $parentUri, string $childPath): bool
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

  private function isValidPathHttp(string $path): bool
  {
    if ($this->hasDisallowedCharacters($path)) {
      return false;
    }

    if ($this->hasDisallowedProtocol($path)) {
      return false;
    }

    return true;
  }

  private function isValidPathFs(string $path): bool
  {
    if ($this->hasDisallowedCharacters($path)) {
      return false;
    }

    if ($this->hasDisallowedProtocol($path)) {
      return false;
    }

    if (str_starts_with($path, '/')) {
      return false;
    }

    if (str_contains($path, '//')) {
      return false;
    }

    return true;
  }

  private function hasDisallowedCharacters(string $path): bool
  {
    $pattern = '/^[A-Za-z0-9\-._~!$&\'()*+,;=:@\/?]*$/';

    return 0 === preg_match($pattern, $path);
  }

  private function hasDisallowedProtocol(string $path): bool
  {
    $pattern = '/^(?<protocol>[a-zA-Z_]*):.*$/';

    $matched = preg_match($pattern, $path, $matches);

    if (0 === $matched) {
      return false;
    }

    return !in_array($matches['protocol'], ['http', 'https']);
  }
}
