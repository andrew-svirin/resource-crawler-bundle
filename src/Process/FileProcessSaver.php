<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ImgNode;
use LogicException;

/**
 * Primitive saver to the file.
 * Very limited usage.
 */
final class FileProcessSaver implements ProcessSaverInterface
{
    private const NODE_TYPE_HTML = 'html';

    private const NODE_TYPE_IMG = 'img';

    private const URI_TYPE_HTTP = 'http';

    private const URI_TYPE_FS = 'fs';

    public function __construct(private readonly string $dir)
    {
    }

    private function taskExists(array $processData, string $taskId): bool
    {
        return in_array($taskId, array_keys($processData['for_processing'])) ||
            in_array($taskId, array_keys($processData['in_process'])) ||
            in_array($taskId, array_keys($processData['processed']));
    }

    public function addForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void
    {
        $processData = $this->readProcessData($process);

        $taskId = $this->genTaskId($task);

        if (!$this->taskExists($processData, $taskId)) {
            $processData['for_processing'][$taskId] = $this->packTask($task);

            $this->writeProcessData($process, $processData);
        }
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

    private function genTaskId(CrawlingTask $task): string
    {
        return hash('sha256', $task->getNode()->getUri()->getPath());
    }

    private function packTask(CrawlingTask $task): array
    {
        if ($task->getNode() instanceof HtmlNode) {
            $nodeType = self::NODE_TYPE_HTML;
        } elseif ($task->getNode() instanceof ImgNode) {
            $nodeType = self::NODE_TYPE_IMG;
        } else {
            throw new LogicException('Incorrect node type.');
        }

        if ($task->getNode()->getUri() instanceof HttpUri) {
            $uriType = self::URI_TYPE_HTTP;
        } elseif ($task->getNode()->getUri() instanceof FsUri) {
            $uriType = self::URI_TYPE_FS;
        } else {
            throw new LogicException('Incorrect uri type.');
        }

        return [
            'type' => $nodeType,
            'uri'  => [
                'type' => $uriType,
                'path' => $task->getNode()->getUri()->getPath(),
            ],
        ];
    }
}
