<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution;

/**
 * Path substitutor.
 * Substitute path string.
 *
 * @interal
 */
final class PathSubstitutor
{
  public function substitute(PathSubstitution $pathSubstitution, string $path): string
  {
    $patterns     = [];
    $replacements = [];

    foreach ($pathSubstitution->getSubstitutions() as $substitution) {
      $patterns[]     = $substitution->pattern();
      $replacements[] = $substitution->replacement();
    }

    return preg_replace($patterns, $replacements, $path);
  }
}
