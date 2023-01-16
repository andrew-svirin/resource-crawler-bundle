<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Analyze;

/**
 * Crawling process analyze.
 */
final class CrawlingAnalyze
{
  /**
   * @param array<string, int> $statusCounts
   */
  public function __construct(private readonly array $statusCounts)
  {
  }

  /**
   * @return array<string, int>
   */
  public function getStatusCounts(): array
  {
    return $this->statusCounts;
  }
}
