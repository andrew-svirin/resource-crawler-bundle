<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * PathRegex model.
 *
 * @interal
 */
final class PathRegex
{
    private array $allowedExpressions = [];

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

    public function getAllowedExpressions(): array
    {
        return $this->allowedExpressions;
    }

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
