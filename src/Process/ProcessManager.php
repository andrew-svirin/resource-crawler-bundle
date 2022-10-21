<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;

/**
 * Facade for processes and tasks.
 *
 * @interal
 */
final class ProcessManager
{
    public function __construct(
        private readonly ProcessStoreInterface $processStore,
        private readonly ProcessFactory $processFactory,
        private readonly TaskFactory $taskFactory
    ) {
    }

    public function load(Resource $resource): CrawlingProcess
    {
        $process = $this->processFactory->create($resource);
        $node    = $resource->getRoot();

        $this->pushTask($process, $node);

        return $process;
    }

    public function popTask(CrawlingProcess $process): ?CrawlingTask
    {
        $task = $this->processStore->popForProcessingTask($process);
        if (!empty($task)) {
            return $task;
        }

        return $this->processStore->popInProcessTask($process);
    }

    public function pushTask(CrawlingProcess $process, NodeInterface $node): void
    {
        $task = $this->taskFactory->create($process, $node);

        $this->processStore->pushForProcessingTask($process, $task);
    }

    public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
    {

    }
}
