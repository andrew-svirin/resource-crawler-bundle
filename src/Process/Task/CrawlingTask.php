<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Task;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;

/**
 * Model implements crawling task.
 *
 * @interal
 */
final class CrawlingTask
{
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
}
