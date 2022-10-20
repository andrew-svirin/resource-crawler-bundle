<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

/**
 * Interface for process saving.
 */
interface ProcessSaverInterface
{
    public function addForProcessingTask(CrawlingProcess $process, CrawlingTask $task);
}
