<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use Closure;

/**
 * Closure to operate store.
 */
final class OperateStoreClosure
{
  private Closure $closure;

  public function __construct(private readonly FileProcessStore $newThis, callable $callable)
  {
    $this->closure = $callable(...);
  }

  public function call(): bool
  {
    return $this->closure->call($this->newThis);
  }
}
