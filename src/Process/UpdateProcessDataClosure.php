<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use Closure;

/**
 * Closure to update process data.
 */
final class UpdateProcessDataClosure
{
  private Closure $closure;

  public function __construct(private readonly FileProcessStore $newThis, callable $callable)
  {
    $this->closure = $callable(...);
  }

  /**
   * @param array<string, array<string, string|array<string, string>>> $processData
   * @return array<string, array<string, string|array<string, string>>> | null
   *  Return `null` - when avoid writing.
   */
  public function call(array $processData): ?array
  {
    return $this->closure->call($this->newThis, $processData);
  }
}
