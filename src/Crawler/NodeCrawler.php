<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\Ref;
use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefManager;
use AndrewSvirin\ResourceCrawlerBundle\Document\DocumentManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;
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
    private readonly DocumentManager $documentManager,
    private readonly RefManager $refManager,
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

    if ($this->resourceManager->isNotHtmlNode($node)) {
      return;
    }

    $document = $this->documentManager->createDocument($node->getResponse()->getContent());

    if (null === $document) {
      return;
    }

    $node->setDocument($document);
  }

  private function crawlImgNode(ImgNode $node): void
  {
    $response = $this->resourceManager->readUri($node->getUri());

    $node->setResponse($response);
  }

  /**
   * @return Ref[]
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
   * @return Ref[]
   */
  private function walkHtmlNode(HtmlNode $node): iterable
  {
    $doc = $node->getDocument();

    if (empty($doc)) {
      return;
    }

    foreach ($this->documentManager->extractRefElements($doc) as $element) {
      yield $this->refManager->createRef($element);
    }
  }

  public function createRefNode(Ref $ref, ResourceInterface $resource): NodeInterface
  {
    if ($this->documentManager->isElementAnchor($ref->getElement())) {
      $node = $this->resourceManager->createHtmlNode($resource, $ref->getNormalizedPath());
    } elseif ($this->documentManager->isElementImg($ref->getElement())) {
      $node = $this->resourceManager->createImgNode($resource, $ref->getNormalizedPath());
    } else {
      throw new LogicException('Node name not handled.');
    }

    return $node;
  }
}
