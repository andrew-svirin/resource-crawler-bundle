<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex;

/**
 * Path regex matcher.
 * Matches path with regex.
 *
 * @interal
 */
final class PathRegexMatcher
{
  public function isMatching(PathRegex $pathRegex, string $path): bool
  {
    preg_match_all($pathRegex->getExpression(), $path, $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

    /** @var array<int, array<int, array<int, null|string>>> $matches */
    $disallowedMatches = &$matches[1];
    $allowedMatches    = &$matches[2];

    return !$this->isMatchingMatch($disallowedMatches) && $this->isMatchingMatch($allowedMatches);
  }

  /**
   * @param array<array<int, null|string>> $matches
   */
  private function isMatchingMatch(array $matches): bool
  {
    if (empty($matches)) {
      return false;
    }

    foreach ($matches as $match) {
      if (null !== $match[0]) {
        return true;
      }
    }

    return false;
  }
}
