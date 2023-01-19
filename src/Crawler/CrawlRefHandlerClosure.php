<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use Closure;
use DOMElement;

/**
 * Crawler for node.
 *
 * @interal
 */
final class CrawlRefHandlerClosure implements RefHandlerClosureInterface
{
  private Closure $closure;

  public function __construct(private readonly ResourceCrawler $newThis, callable $callable)
  {
    $this->closure = $callable(...);
  }

  public function call(DOMElement $ref, bool $isValidPath, ?string $normalizedPath, ?bool $isPerformablePath): void
  {
    $this->closure->call($this->newThis, $ref, $isValidPath, $normalizedPath, $isPerformablePath);
  }
}
