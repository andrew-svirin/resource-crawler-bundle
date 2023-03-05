<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document;

use AndrewSvirin\ResourceCrawlerBundle\Document\Html\HtmlExtractor;
use DOMDocument;
use DOMElement;

/**
 * Manager for document domain.
 *
 * @interal
 */
final class DocumentManager
{
  public function __construct(
    private readonly HtmlExtractor $htmlExtractor,
    private readonly DocumentResolver $documentComposer
  ) {
  }

  public function createDocument(string $content): ?DOMDocument
  {
    return $this->documentComposer->resolve($content);
  }

  /**
   * @return \DOMElement[]
   */
  public function extractRefElements(DOMDocument $dom): iterable
  {
    yield from $this->htmlExtractor->extractAnchors($dom);
    yield from $this->htmlExtractor->extractImgs($dom);
  }

  public function getElementPath(DOMElement $element): string
  {
    return $this->htmlExtractor->getElementPath($element);
  }

  public function setElementPath(DOMElement $element, string $path): void
  {
    $this->htmlExtractor->setElementPath($element, $path);
  }

  public function isElementImg(DOMElement $element): bool
  {
    return $this->htmlExtractor->isElementImg($element);
  }

  public function isElementAnchor(DOMElement $element): bool
  {
    return $this->htmlExtractor->isElementAnchor($element);
  }
}
