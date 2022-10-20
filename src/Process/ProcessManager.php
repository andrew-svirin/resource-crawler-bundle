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
        private readonly ProcessSaverInterface $processSaver,
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

    public function popTask(CrawlingProcess $process): CrawlingTask
    {
        return $this->taskFactory->create($node);
    }

    public function pushTask(CrawlingProcess $process, NodeInterface $node): void
    {
        $task = $this->taskFactory->create($node);

        $this->processSaver->addForProcessingTask($process, $task);
    }

    public function destroyTask(CrawlingProcess $process, CrawlingTask $task): void
    {

    }

    private function resolveProcessId(HttpResource $resource): string
    {
        return preg_replace('/[^[:alnum:]]/', '_', $resource->getRoot()->getUri()->getPath());
    }
}
