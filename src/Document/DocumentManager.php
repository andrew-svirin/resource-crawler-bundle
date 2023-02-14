<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document;

use AndrewSvirin\ResourceCrawlerBundle\Document\Html\HtmlExtractor;
use DOMDocument;

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
  public function extractRefs(DOMDocument $dom): iterable
  {
    yield from $this->htmlExtractor->extractAnchors($dom);
    yield from $this->htmlExtractor->extractImgs($dom);
  }
}
