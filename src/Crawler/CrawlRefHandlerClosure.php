<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;
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

  public function call(RefPath $refPath, CrawlingTask $task): void
  {
    $this->closure->call($this->newThis, $refPath, $task);
  }
}
