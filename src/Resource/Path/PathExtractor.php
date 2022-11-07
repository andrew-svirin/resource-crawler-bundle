<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * Path components extractor.
 *
 * @interal
 */
final class PathExtractor
{
  public function extractScheme(string $path): ?string
  {
    $pattern = '/^(?<scheme>[a-zA-Z_]*):.*$/';

    $matched = preg_match($pattern, $path, $matches);

    if (0 === $matched) {
      return null;
    }

    return $matches['scheme'];
  }

  public function extractBase64EncodedData(string $path): ?string
  {
    $pattern = '/data:(.*);base64,(?<base64EncodedData>.*)/';

    $matched = preg_match($pattern, $path, $matches);

    if (0 === $matched) {
      return null;
    }

    return $matches['base64EncodedData'];
  }
}
