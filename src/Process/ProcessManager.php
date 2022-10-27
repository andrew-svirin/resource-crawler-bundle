<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;

/**
 * Facade for Process domain.
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

    public function loadProcess(Resource $resource): CrawlingProcess
    {
        $process = $this->processFactory->create($resource);
        $node    = $resource->getRoot();

        $this->pushTaskIfNotExists($process, $node);

        return $process;
    }

    public function killProcess(Resource $resource): void
    {
        $process = $this->processFactory->create($resource);

        $this->processStore->deleteProcess($process);
    }

    public function popTask(CrawlingProcess $process): ?CrawlingTask
    {
        $task = $this->processStore->popForProcessingTask($process);

        if (!empty($task)) {
            return $task;
        }

        return $this->processStore->popInProcessTask($process);
    }

    public function pushTaskIfNotExists(CrawlingProcess $process, NodeInterface $node): void
    {
        $task = $this->taskFactory->create($process, $node);

        $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

        if ($this->processStore->taskExists($process, $task)) {
            return;
        }

        $this->processStore->pushForProcessingTask($process, $task);
    }

    public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $this->processStore->pushProcessedTask($process, $task);
    }

    public function ignoreTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $this->processStore->pushIgnoredTask($process, $task);
    }
}
