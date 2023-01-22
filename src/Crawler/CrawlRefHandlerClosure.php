<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefHandlerClosureInterface;
use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;
use Closure;

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

  public function call(RefPath $refPath): void
  {
    $this->closure->call($this->newThis, $refPath);
  }
}
