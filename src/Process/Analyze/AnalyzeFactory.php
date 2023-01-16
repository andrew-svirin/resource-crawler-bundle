<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Analyze;

/**
 * Factory for the process analyze.
 *
 * @interal
 */
final class AnalyzeFactory
{
  /**
   * @param array<string, int> $statusCounts
   */
  public function create(array $statusCounts): CrawlingAnalyze
  {
    return new CrawlingAnalyze($statusCounts);
  }
}
