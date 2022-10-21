<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Extractor\HtmlExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\FsResource;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpResource;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;
use LogicException;
use RuntimeException;

final class ResourceCrawler
{
    public function __construct(
        private readonly ResourceManager $resourceManager,
        private readonly ProcessManager $processManager,
        private readonly HtmlExtractor $htmlExtractor
    ) {
    }

    public function crawlHttpResource(string $url): void
    {
        $resource = $this->resourceManager->createHttpHtmlResource($url);

        $process = $this->processManager->load($resource);

        $task = $this->processManager->popTask($process);

        $this->performTask($task);

        $this->processManager->destroyTask($process, $task);
    }

    private function performTask(CrawlingTask $task): void
    {
        $node = $task->getNode();

        $this->crawlNode($task, $node);
    }

    /**
     * Crawl node.
     * Here is distinguishing task type.
     */
    private function crawlNode(CrawlingTask $task, NodeInterface $node): void
    {
        if ($node instanceof HtmlNode) {
            $this->crawlHtmlNode($task, $node);
        } elseif ($node instanceof ImgNode) {
            throw new RuntimeException('Handle img.');
        } else {
            throw new LogicException('Incorrect node.');
        }
    }

    /**
     * Walk other html nodes graph.
     */
    private function crawlHtmlNode(CrawlingTask $task, HtmlNode $node): void
    {
        $html = $this->resourceManager->readUri($node->getUri());

        $node->setContent($html);

        $document = $this->htmlExtractor->extractDocument($node->getContent());

        $node->setDocument($document);

        $this->processAnchors($task, $node);

        $this->processImgs($task, $node);
    }

    private function processAnchors(CrawlingTask $task, HtmlNode $node): void
    {
        foreach ($this->htmlExtractor->extractAHrefs($node->getDocument()) as $path) {
            if ($task->getProcess()->getResource() instanceof HttpResource) {
                $node = $this->resourceManager->createHttpHtmlNode($path);
            } elseif ($task->getProcess()->getResource() instanceof FsResource) {
                $node = $this->resourceManager->createFsHtmlNode($path);
            }

            $this->processManager->pushTask($task->getProcess(), $node);
        }
    }

    private function processImgs(CrawlingTask $task, HtmlNode $node): void
    {
        foreach ($this->htmlExtractor->extractImgSrcs($node->getDocument()) as $path) {
            if ($task->getProcess()->getResource() instanceof HttpResource) {
                $node = $this->resourceManager->createHttpImgNode($path);
            } elseif ($task->getProcess()->getResource() instanceof FsResource) {
                $node = $this->resourceManager->createFsImgNode($path);
            }

            $this->processManager->pushTask($task->getProcess(), $node);
        }
    }
}
