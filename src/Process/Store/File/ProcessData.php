<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Store\File;

/**
 * Process Data wrapper for process data.
 */
final class ProcessData
{
  /**
   * @param array<string, array<string, array{
   *   uri: array{type: string, path: string},type: string, code: null|int
   * }>> $data
   */
  public function __construct(public array $data)
  {
  }
}
