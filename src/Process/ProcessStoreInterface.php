<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;

/**
 * Interface for process store.
 */
interface ProcessStoreInterface
{
    /**
     * Push task for_processing stack.
     */
    public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void;

    /**
     * Pop task from for_processing stack.
     */
    public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask;

    /**
     * Pop task from in_process stack.
     */
    public function popInProcessTask(CrawlingProcess $process): ?CrawlingTask;

    /**
     * Does task already exists.
     */
    public function taskExists(CrawlingProcess $process, CrawlingTask $task): bool;
}
