<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Task;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;

/**
 * Factory for the task.
 *
 * @interal
 */
final class TaskFactory
{
  public function create(CrawlingProcess $process, NodeInterface $node): CrawlingTask
  {
    return new CrawlingTask($process, $node);
  }
}
