<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskPacker;

/**
 * Primitive store in the file.
 * Very limited usage.
 */
final class FileProcessStore implements ProcessStoreInterface
{
    public function __construct(
        private readonly string $dir,
        private readonly TaskPacker $taskPacker,
        private readonly TaskFactory $taskFactory
    ) {
    }

    public function taskExists(CrawlingProcess $process, CrawlingTask $task): bool
    {
        $processData = $this->readProcessData($process);

        $taskHash = $this->genTaskHash($task);

        return in_array($taskHash, array_keys($processData[CrawlingTask::STATUS_FOR_PROCESSING])) ||
            in_array($taskHash, array_keys($processData[CrawlingTask::STATUS_IN_PROCESS])) ||
            in_array($taskHash, array_keys($processData[CrawlingTask::STATUS_PROCESSED])) ||
            in_array($taskHash, array_keys($processData[CrawlingTask::STATUS_IGNORED]));
    }

    public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $processData = $this->readProcessData($process);

        $taskHash = $this->genTaskHash($task);

        $processData[CrawlingTask::STATUS_FOR_PROCESSING][$taskHash] = $this->packTask($task);

        $this->writeProcessData($process, $processData);
    }

    public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
    {
        $processData = $this->readProcessData($process);

        $taskHash = array_key_last($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

        if (empty($taskHash)) {
            return null;
        }

        $packedTask = array_pop($processData[CrawlingTask::STATUS_FOR_PROCESSING]);

        $processData[CrawlingTask::STATUS_IN_PROCESS][$taskHash] = $packedTask;

        $this->writeProcessData($process, $processData);

        $task = $this->unpackTask($process, $packedTask);

        $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

        return $task;
    }

    public function popInProcessTask(CrawlingProcess $process): ?CrawlingTask
    {
        $processData = $this->readProcessData($process);

        $packedTask = array_pop($processData[CrawlingTask::STATUS_IN_PROCESS]);

        if (empty($packedTask)) {
            return null;
        }

        $task = $this->unpackTask($process, $packedTask);

        $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

        return $task;
    }

    public function pushProcessedTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $this->pushTask($process, $task, CrawlingTask::STATUS_PROCESSED);
    }

    public function pushIgnoredTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $this->pushTask($process, $task, CrawlingTask::STATUS_IGNORED);
    }

    private function pushTask(CrawlingProcess $process, CrawlingTask $task, string $status): void
    {
        $processData = $this->readProcessData($process);

        $taskHash = $this->genTaskHash($task);

        $packedTask = $processData[$task->getStatus()][$taskHash];

        unset($processData[$task->getStatus()][$taskHash]);

        $processData[$status][$taskHash] = $packedTask;

        $task->setStatus($status);

        $this->writeProcessData($process, $processData);
    }

    /**
     * @return array<string, array<string, string|array<string, string>>>
     */
    private function readProcessData(CrawlingProcess $process): array
    {
        $content = $this->readProcessContent($process);

        return $content ? json_decode($content, true) : $this->defaultProcessData();
    }

    private function readProcessContent(CrawlingProcess $process): ?string
    {
        $filename = $this->getProcessFilename($process);

        return file_exists($filename) ? file_get_contents($filename) : null;
    }

    private function deleteProcessContent(CrawlingProcess $process): void
    {
        $filename = $this->getProcessFilename($process);

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * @param array<string, array<string, string|array<string, string>>> $processData
     */
    private function writeProcessData(CrawlingProcess $process, array $processData): void
    {
        $content = json_encode($processData, JSON_PRETTY_PRINT);

        $this->writeProcessContent($process, $content);
    }

    private function writeProcessContent(CrawlingProcess $process, string $content): void
    {
        $filename = $this->getProcessFilename($process);

        $this->prepareFileDir($filename);

        file_put_contents($filename, $content);
    }

    private function prepareFileDir(string $filename): void
    {
        $fileDir = dirname($filename);

        if (!file_exists($fileDir)) {
            mkdir($fileDir, 0775, true);
        }
    }

    /**
     * @return array<string, array<null>>
     */
    private function defaultProcessData(): array
    {
        return [
            CrawlingTask::STATUS_FOR_PROCESSING => [],
            CrawlingTask::STATUS_IN_PROCESS     => [],
            CrawlingTask::STATUS_PROCESSED      => [],
            CrawlingTask::STATUS_IGNORED        => [],
        ];
    }

    private function getProcessFilename(CrawlingProcess $process): string
    {
        return $this->dir . '/' . $process->getId() . '.json';
    }

    private function genTaskHash(CrawlingTask $task): string
    {
        return hash('sha256', $task->getNode()->getUri()->getPath());
    }

    /**
     * @param \AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask $task
     * @return array<string, string|array<string, string>>
     */
    private function packTask(CrawlingTask $task): array
    {
        $nodeType = $this->taskPacker->packNodeType($task->getNode());
        $uriType  = $this->taskPacker->packUriType($task->getNode()->getUri());

        return [
            'type' => $nodeType,
            'uri'  => [
                'type' => $uriType,
                'path' => $task->getNode()->getUri()->getPath(),
            ],
        ];
    }

    /**
     * @param array<string, string|array<string, string>> $packedTask
     */
    private function unpackTask(CrawlingProcess $process, array $packedTask): CrawlingTask
    {
        $uri  = $this->taskPacker->unpackUri($packedTask['uri']['type'], $packedTask['uri']['path']);
        $node = $this->taskPacker->unpackNode($packedTask['type'], $uri);

        return $this->taskFactory->create($process, $node);
    }

    public function deleteProcess(CrawlingProcess $process): void
    {
        $this->deleteProcessContent($process);
    }
}
