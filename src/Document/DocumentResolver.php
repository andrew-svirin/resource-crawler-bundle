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
  private const ENCODING_DEFAULT = 'utf-8';

  public function resolve(string $html): ?DOMDocument
  {
    if (empty($html)) {
      return null;
    }

    $detect = $this->detectEncoding($html);

    $dom = new DOMDocument('1.0', $detect);

    $dom->substituteEntities = false;

    $load = @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if (!$load) {
      return null;
    }

    return $dom;
  }

  private function detectEncoding(string $html): string
  {
    preg_match('/charset=([^ \"]*)/', $html, $matches);

    return isset($matches[1]) ? strtolower($matches[1]) : self::ENCODING_DEFAULT;
  }
}
