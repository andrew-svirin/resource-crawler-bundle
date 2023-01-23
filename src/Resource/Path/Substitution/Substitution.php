<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution;

/**
 * Single substitution.
 *
 * @interal
 */
final class Substitution
{
  public function __construct(private readonly string $pattern, private readonly string $replacement)
  {
  }

  public function pattern(): string
  {
    return $this->pattern;
  }

  public function replacement(): string
  {
    return $this->replacement;
  }
}
