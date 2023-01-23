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
   * @param array<array<string>> $substRules
   */
  public function create(array $substRules): PathSubstitution
  {
    $substitutions = [];

    foreach ($substRules as $substRule) {
      $substitutions[] = new Substitution($substRule[0], $substRule[1]);
    }

    return new PathSubstitution($substitutions);
  }
}
