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

        return in_array($taskHash, array_keys($processData['for_processing'])) ||
            in_array($taskHash, array_keys($processData['in_process'])) ||
            in_array($taskHash, array_keys($processData['processed']));
    }

    public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $processData = $this->readProcessData($process);

        $taskHash = $this->genTaskHash($task);

        $processData['for_processing'][$taskHash] = $this->packTask($task);

        $this->writeProcessData($process, $processData);
    }

    public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
    {
        $processData = $this->readProcessData($process);

        $taskHash   = array_key_last($processData['for_processing']);
        $packedTask = array_pop($processData['for_processing']);

        if (empty($packedTask)) {
            return null;
        }

        $processData['in_process'][$taskHash] = $packedTask;

        $this->writeProcessData($process, $processData);

        return $this->unpackTask($process, $packedTask);
    }

    public function popInProcessTask(CrawlingProcess $process): ?CrawlingTask
    {
        $processData = $this->readProcessData($process);

        $packedTask = array_pop($processData['in_process']);

        if (empty($packedTask)) {
            return null;
        }

        return $this->unpackTask($process, $packedTask);
    }

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

    private function defaultProcessData(): array
    {
        return [
            'for_processing' => [],
            'in_process'     => [],
            'processed'      => [],
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

    private function unpackTask(CrawlingProcess $process, array $packedTask): CrawlingTask
    {
        $uri  = $this->taskPacker->unpackUri($packedTask['uri']['type'], $packedTask['uri']['path']);
        $node = $this->taskPacker->unpackNode($packedTask['type'], $uri);

        return $this->taskFactory->create($process, $node);
    }
}
