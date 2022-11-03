<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * PathRegex model.
 *
 * @interal
 */
final class PathRegex
{
  /**
   * @var string[]
   */
  private array $allowedExpressions = [];

  /**
   * @var string[]
   */
  private array $disallowedExpressions = [];

  private string $expression;

  public function addAllowed(string $expression): void
  {
    $this->allowedExpressions[] = $expression;
  }

  public function addDisallowed(string $expression): void
  {
    $this->disallowedExpressions[] = $expression;
  }

  /**
   * @return string[]
   */
  public function getAllowedExpressions(): array
  {
    return $this->allowedExpressions;
  }

  /**
   * @return string[]
   */
  public function getDisallowedExpressions(): array
  {
    return $this->disallowedExpressions;
  }

  public function getExpression(): string
  {
    return $this->expression;
  }

  public function setExpression(string $expression): void
  {
    $this->expression = $expression;
  }
}
