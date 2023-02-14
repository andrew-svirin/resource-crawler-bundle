<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document;

use DOMDocument;

/**
 * Resolver for document.
 *
 * @interal
 */
final class DocumentResolver
{
  public function resolve(string $html): ?DOMDocument
  {
    $dom = new DOMDocument;

    $dom->substituteEntities = false;

    $sourceUtf8 = mb_convert_encoding($html, 'html-entities', 'utf-8');

    if (empty($sourceUtf8)) {
      return null;
    }

    $load = @$dom->loadHTML($sourceUtf8, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if (!$load) {
      return null;
    }

    return $dom;
  }
}
