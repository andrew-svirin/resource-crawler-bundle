<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Reader\ResourceReader;
use AndrewSvirin\ResourceCrawlerBundle\Resource\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceFactory;
use LogicException;

final class ResourceCrawler
{
    public function __construct(
        private readonly ResourceReader $reader,
        private readonly ResourceFactory $resourceFactory,
        private readonly ProcessManager $processManager,
        private readonly HtmlExtractor $htmlExtractor
    ) {
    }

    public function crawlHttpResource(string $url): void
    {
        $resource = $this->resourceFactory->createHttp($url);

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
    private function crawlNode(Node $node): void
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
        $html = $this->reader->read($node->getUri());

        $node->setContent($html);

        $links = $this->htmlExtractor->extractLinks($node);

        dd('Links', $links);
        // TODO: Implement crawl() method.
    }
}
