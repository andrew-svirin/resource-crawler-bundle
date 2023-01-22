<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution;

/**
 * Single substitution.
 *
 * @interal
 */
final class Substitution
{
  public function __construct(private readonly string $fromPattern, private readonly string $toPattern)
  {
  }

  public function fromPattern(): string
  {
    return $this->fromPattern;
  }

  public function toPattern(): string
  {
    return $this->toPattern;
  }
}
