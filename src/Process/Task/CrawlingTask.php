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
    public const STATUS_FOR_PROCESSING = 'for_processing';

    public const STATUS_IN_PROCESS = 'in_process';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_IGNORED = 'ignored';

    private string $status;

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
}
