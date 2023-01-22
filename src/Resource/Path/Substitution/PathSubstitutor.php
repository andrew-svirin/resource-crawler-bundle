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
    $fromPatterns = [];
    $toPatterns   = [];

    foreach ($pathSubstitution->getSubstitutions() as $substitution) {
      $fromPatterns[] = $substitution->fromPattern();
      $toPatterns[]   = $substitution->toPattern();
    }

    return preg_replace($fromPatterns, $toPatterns, $path);
  }
}
