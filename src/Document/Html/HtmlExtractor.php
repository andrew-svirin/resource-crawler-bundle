<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document\Html;

use DOMDocument;
use DOMElement;
use LogicException;

/**
 * Service to extract elements from nodes.
 *
 * @interal
 */
final class HtmlExtractor
{
  /**
   * @return DOMElement[]
   */
  public function extractAnchors(DOMDocument $dom): iterable
  {
    $nodeList = $dom->getElementsByTagName('a');

    /** @var \DOMNode|null $node */
    foreach ($nodeList as $node) {
      if ($node instanceof DOMElement) {
        yield $node;
      }
    }
  }

  /**
   * @return DOMElement[]
   */
  public function extractImgs(DOMDocument $dom): iterable
  {
    $nodeList = $dom->getElementsByTagName('img');

    /** @var \DOMNode $node */
    foreach ($nodeList as $node) {
      if ($node instanceof DOMElement) {
        yield $node;
      }
    }
  }

  public function getElementPath(DOMElement $dom): string
  {
    if ($this->isElementAnchor($dom)) {
      $path = $dom->getAttribute('href');
    } elseif ($this->isElementImg($dom)) {
      $path = $dom->getAttribute('src');
    } else {
      throw new LogicException('Node name not handled.');
    }

    return $path;
  }

  public function setElementPath(DOMElement $dom, string $path): void
  {
    if ($this->isElementAnchor($dom)) {
      $dom->setAttribute('href', $path);
    } elseif ($this->isElementImg($dom)) {
      $dom->setAttribute('src', $path);
    } else {
      throw new LogicException('Node name not handled.');
    }
  }

  public function isElementImg(DOMElement $dom): bool
  {
    return 'img' === $dom->nodeName;
  }

  public function isElementAnchor(DOMElement $dom): bool
  {
    return 'a' === $dom->nodeName;
  }
}
