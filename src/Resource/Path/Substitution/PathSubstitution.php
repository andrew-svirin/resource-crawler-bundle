<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution;

/**
 * Path substitution rules.
 *
 * @interal
 */
final class PathSubstitution
{
  /**
   * @param Substitution[] $substitutions
   */
  public function __construct(private readonly array $substitutions)
  {
  }

  /**
   * @return Substitution[]
   */
  public function getSubstitutions(): array
  {
    return $this->substitutions;
  }
}
