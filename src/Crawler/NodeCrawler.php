<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;
use AndrewSvirin\ResourceCrawlerBundle\Document\DocumentManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Path;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceManager;
use DOMElement;
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
   * @return RefPath[]
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
   * @return RefPath[]
   */
  private function walkHtmlNode(HtmlNode $node): iterable
  {
    $doc = $node->getDocument();

    if (empty($doc)) {
      return;
    }

    foreach ($this->documentManager->extractRefs($doc) as $ref) {
      $refPath = new RefPath($ref);

      $path = $this->decomposeRefPath($ref);

      $refPath->setValid($this->resourceManager->isValidPath($node->getUri(), $path));

      if ($refPath->isValid()) {
        $refPath->setNormalizedPath($this->resourceManager->normalizePath($node->getUri(), $path));
      }

      yield $refPath;
    }
  }

  private function decomposeRefPath(DOMElement $ref): Path
  {
    if ('a' === $ref->nodeName) {
      $path = $this->resourceManager->decomposePath($ref->getAttribute('href'));
    } elseif ('img' === $ref->nodeName) {
      $path = $this->resourceManager->decomposePath($ref->getAttribute('src'));
    } else {
      throw new LogicException('Node name not handled.');
    }

    return $path;
  }

  public function createRefNode(DOMElement $ref, ResourceInterface $resource, string $normalizedPath): NodeInterface
  {
    if ('a' === $ref->nodeName) {
      $node = $this->resourceManager->createHtmlNode($resource, $normalizedPath);
    } elseif ('img' === $ref->nodeName) {
      $node = $this->resourceManager->createImgNode($resource, $normalizedPath);
    } else {
      throw new LogicException('Node name not handled.');
    }

    return $node;
  }

//  private function isImagePath(string $path): bool
//  {
//    $supported_image = array(
//      'gif',
//      'jpg',
//      'jpeg',
//      'png'
//    );
//
//    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
//
//    return in_array()
//  }
}
