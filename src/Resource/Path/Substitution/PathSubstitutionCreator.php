<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution;

/**
 * Path substitution creator.
 *
 * @interal
 */
final class PathSubstitutionCreator
{
  /**
   * @param array<string,string> $substRules
   */
  public function create(array $substRules): PathSubstitution
  {
    $substitutions = [];

    foreach ($substRules as $fromPattern => $toPattern) {
      $substitutions[] = new Substitution($fromPattern, $toPattern);
    }

    return new PathSubstitution($substitutions);
  }
}
