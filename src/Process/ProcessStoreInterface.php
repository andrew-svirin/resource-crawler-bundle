<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

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
}
