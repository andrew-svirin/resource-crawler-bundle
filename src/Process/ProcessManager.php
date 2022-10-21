<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpResource;
use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;

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

    public function load(HttpResource $resource): CrawlingProcess
    {
        $processId = $this->resolveProcessId($resource);

        $process = $this->processFactory->create($processId);
        $node    = $resource->getRoot();

        $this->pushTask($process, $node);

        return $process;
    }

    public function popTask(CrawlingProcess $process): ?CrawlingTask
    {
        $task = $this->processStore->popForProcessingTask($process);
        if(!empty($task)){
            return $task;
        }

        return $this->processStore->popInProcessTask($process);
    }

    public function pushTask(CrawlingProcess $process, NodeInterface $node): void
    {
        $task = $this->taskFactory->create($node);

        $this->processStore->pushForProcessingTask($process, $task);
    }

    public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
    {

    }

    private function resolveProcessId(HttpResource $resource): string
    {
        return preg_replace('/[^[:alnum:]]/', '_', $resource->getRoot()->getUri()->getPath());
    }
}
