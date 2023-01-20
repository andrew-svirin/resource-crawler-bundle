<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Document\DocumentManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
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
    private readonly DocumentManager $documentManager
  ) {
  }

  /**
   * Crawl node.
   * Here is distinguishing task type.
   */
  public function crawl(NodeInterface $node): void
  {
    if ($node instanceof HtmlNode) {
      $this->crawlHtmlNode($node);
    } elseif ($node instanceof ImgNode) {
      $this->crawlImgNode($node);
    } else {
      throw new LogicException('Incorrect node.');
    }
  }

  /**
   * Walk other html nodes graph.
   */
  private function crawlHtmlNode(HtmlNode $node): void
  {
    $response = $this->resourceManager->readUri($node->getUri());

    $node->setResponse($response);

    if ($this->resourceManager->isNotSuccessNode($node)) {
      return;
    }

    $document = $this->documentManager->createDocument($node->getResponse()->getContent());

    $node->setDocument($document);
  }

  private function crawlImgNode(ImgNode $node): void
  {
    $response = $this->resourceManager->readUri($node->getUri());

    $node->setResponse($response);
  }

  /**
   * @return array<array<\DOMElement | bool | string | null>>
   */
  public function walkNode(NodeInterface $node): iterable
  {
    if ($node instanceof HtmlNode) {
      yield from $this->walkHtmlNode($node);
    } elseif ($node instanceof ImgNode) {
      return;
    } else {
      throw new LogicException('Incorrect node.');
    }
  }

  /**
   * @return array<array<\DOMElement | bool | string | null>>
   */
  private function walkHtmlNode(HtmlNode $node): iterable
  {
    $doc = $node->getDocument();

    if (empty($doc)) {
      return;
    }

    foreach ($this->documentManager->extractRefs($doc) as $ref) {
      if ('a' === $ref->nodeName) {
        $path = $this->resourceManager->decomposePath($ref->getAttribute('href'));
      } elseif ('img' === $ref->nodeName) {
        $path = $this->resourceManager->decomposePath($ref->getAttribute('src'));
      } else {
        throw new LogicException('Node name not handled.');
      }

      $isValidPath = $this->resourceManager->isValidPath($node->getUri(), $path);

      $normalizedPath = $isValidPath ? $this->resourceManager->normalizePath($node->getUri(), $path) : null;

      yield [$ref, $isValidPath, $normalizedPath];
    }
  }
}
