<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Store\File;

use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStoreInterface;
use Closure;

/**
 * Closure to update process data.
 */
final class UpdateProcessDataClosure
{
  private Closure $closure;

  public function __construct(private readonly ProcessStoreInterface $newThis, callable $callable)
  {
    $this->closure = $callable(...);
  }

  /**
   * @return bool - `false` - when avoid writing.
   */
  public function call(ProcessData $processData): bool
  {
    return $this->closure->call($this->newThis, $processData);
  }
}
