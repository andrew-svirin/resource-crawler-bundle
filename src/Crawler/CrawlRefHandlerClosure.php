<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\Ref;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
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

  public function call(Ref $ref, CrawlingTask $task): void
  {
    $this->closure->call($this->newThis, $ref, $task);
  }
}
