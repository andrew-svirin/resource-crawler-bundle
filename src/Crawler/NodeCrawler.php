<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Document\DocumentManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\ImgNode;
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
    private readonly DocumentManager $documentManager
  ) {
  }

  /**
   * Crawl node.
   * Here is distinguishing task type.
   */
  public function crawl(CrawlingTask $task): void
  {
    $node = $task->getNode();

    if ($node instanceof HtmlNode) {
      $this->crawlHtmlNode($task->getProcess(), $node);
    } elseif ($node instanceof ImgNode) {
      $this->crawlImgNode($node);
    } else {
      throw new LogicException('Incorrect node.');
    }
  }

  /**
   * Walk other html nodes graph.
   */
  private function crawlHtmlNode(CrawlingProcess $process, HtmlNode $node): void
  {
    $response = $this->resourceManager->readUri($node->getUri());

    $node->setResponse($response);

    if ($this->resourceManager->isNotSuccessNode($node)) {
      return;
    }

    $document = $this->documentManager->createDocument($node);

    $node->setDocument($document);

    foreach ($this->getAnchorPaths($node) as $anchorPath) {
      $newNode = $this->resourceManager->createHtmlNode($process->getResource(), $anchorPath);

      $this->processManager->pushTaskIfNotExists($process, $newNode);
    }

    foreach ($this->getImgPaths($node) as $imgPath) {
      $newNode = $this->resourceManager->createImgNode($process->getResource(), $imgPath);

      $this->processManager->pushTaskIfNotExists($process, $newNode);
    }
  }

  /**
   * @return string[]
   */
  private function getAnchorPaths(HtmlNode $node): iterable
  {
    foreach ($this->documentManager->extractAHrefs($node) as $pathStr) {
      $path = $this->resourceManager->decomposePath($pathStr);

      if (!$this->resourceManager->isValidPath($node->getUri(), $path)) {
        continue;
      }

      yield $this->resourceManager->normalizePath($node->getUri(), $path);
    }
  }

  /**
   * @return string[]
   */
  private function getImgPaths(HtmlNode $node): iterable
  {
    foreach ($this->documentManager->extractImgSrcs($node) as $pathStr) {
      $path = $this->resourceManager->decomposePath($pathStr);

      if (!$this->resourceManager->isValidPath($node->getUri(), $path)) {
        continue;
      }

      yield $this->resourceManager->normalizePath($node->getUri(), $path);
    }
  }

  private function crawlImgNode(ImgNode $node): void
  {
    $response = $this->resourceManager->readUri($node->getUri());

    $node->setResponse($response);
  }
}
