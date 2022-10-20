<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Extractor\HtmlExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;
use LogicException;

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
        $resource = $this->resourceManager->createHttpResource($url);

        $process = $this->processManager->load($resource);

        $task = $this->processManager->popTask($process);

        $this->performTask($task);

        $this->processManager->destroyTask($process, $task);
    }

    private function performTask(CrawlingTask $task): void
    {
        $node = $task->getNode();

        $this->crawlNode($node);
    }

    /**
     * Crawl node.
     * Here is distinguishing task type.
     */
    private function crawlNode(NodeInterface $node): void
    {
        if ($node instanceof HtmlNode) {
            $this->crawlHtmlNode($node);
        } else {
            throw new LogicException('Incorrect node.');
        }
    }

    /**
     * Walk other html nodes graph.
     */
    private function crawlHtmlNode(HtmlNode $node): void
    {
        $html = $this->resourceManager->readUri($node->getUri());

        $node->setContent($html);

        $links = $this->htmlExtractor->extractLinks($node);

        dd('Links', $links);
        // TODO: Implement crawl() method.
    }
}
