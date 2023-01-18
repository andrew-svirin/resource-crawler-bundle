<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Task;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;

/**
 * Model implements crawling task.
 */
final class CrawlingTask
{
  public const STATUS_FOR_PROCESSING = 'for_processing';

  public const STATUS_IN_PROCESS = 'in_process';

  public const STATUS_PROCESSED = 'processed';

  public const STATUS_IGNORED = 'ignored';

  public const STATUS_ERRORED = 'errored';

  public const ALL_STATUSES = [
    self::STATUS_FOR_PROCESSING,
    self::STATUS_IN_PROCESS,
    self::STATUS_PROCESSED,
    self::STATUS_IGNORED,
    self::STATUS_ERRORED,
  ];

  private string $status;

  /**
   * @var string[]
   */
  private array $pushedForProcessingPaths = [];

  public function __construct(private readonly CrawlingProcess $process, private readonly NodeInterface $node)
  {
  }

  public function getProcess(): CrawlingProcess
  {
    return $this->process;
  }

  public function getNode(): NodeInterface
  {
    return $this->node;
  }

  public function setStatus(string $status): void
  {
    $this->status = $status;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function appendPushedForProcessingPath(string $path): void
  {
    $this->pushedForProcessingPaths[] = $path;
  }

  /**
   * @return string[]
   */
  public function getPushedForProcessingPaths(): array
  {
    return $this->pushedForProcessingPaths;
  }
}
