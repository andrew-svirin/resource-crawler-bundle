<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Store;

use Closure;

/**
 * Closure to operate store.
 */
final class OperateStoreClosure
{
  private Closure $closure;

  public function __construct(private readonly ProcessStoreInterface $newThis, callable $callable)
  {
    $this->closure = $callable(...);
  }

  /**
   * @return bool - Is operate was success.
   */
  public function call(): bool
  {
    return $this->closure->call($this->newThis);
  }
}
