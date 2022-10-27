<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Document\DocumentManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;
use LogicException;

/**
 * Crawler for node.
 *
 * @interal
 */
final class NodeCrawler
{
    public function __construct(
        private readonly ResourceManager $resourceManager,
        private readonly ProcessManager $processManager,
        private readonly DocumentManager $documentManager,
        private readonly PathNormalizer $pathNormalizer
    ) {
    }

    /**
     * Crawl node.
     * Here is distinguishing task type.
     */
    public function crawl(CrawlingTask $task, NodeInterface $node): void
    {
        if ($node instanceof HtmlNode) {
            $this->crawlHtmlNode($task, $node);
        } elseif ($node instanceof ImgNode) {
            $this->crawlImgNode($node);
        } else {
            throw new LogicException('Incorrect node.');
        }
    }

    /**
     * Walk other html nodes graph.
     */
    private function crawlHtmlNode(CrawlingTask $task, HtmlNode $node): void
    {
        $content = $this->resourceManager->readUri($node->getUri());

        $node->setContent($content);

        $document = $this->documentManager->createDocument($node);

        $node->setDocument($document);

        $this->processAnchors($task, $node);

        $this->processImgs($task, $node);
    }

    private function processAnchors(CrawlingTask $task, HtmlNode $node): void
    {
        foreach ($this->documentManager->extractAHrefs($node) as $path) {
            $normalizedPath = $this->pathNormalizer->normalize($node->getUri(), $path);

            $node = $this->resourceManager->createHtmlNode($task->getProcess()->getResource(), $normalizedPath);

            $this->processManager->pushTaskIfNotExists($task->getProcess(), $node);
        }
    }

    private function processImgs(CrawlingTask $task, HtmlNode $node): void
    {
        foreach ($this->documentManager->extractImgSrcs($node) as $path) {
            $normalizedPath = $this->pathNormalizer->normalize($node->getUri(), $path);

            $node = $this->resourceManager->createImgNode($task->getProcess()->getResource(), $normalizedPath);

            $this->processManager->pushTaskIfNotExists($task->getProcess(), $node);
        }
    }

    private function crawlImgNode(ImgNode $node): void
    {
        $content = $this->resourceManager->readUri($node->getUri());

        $node->setContent($content);
    }
}
