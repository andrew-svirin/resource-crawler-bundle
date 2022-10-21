<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Extractor;

use DOMDocument;

/**
 * Service to extract elements from nodes.
 *
 * @interal
 */
final class HtmlExtractor
{
    public function extractDocument(string $html): DOMDocument
    {
        $dom = new DOMDocument;

        $dom->substituteEntities = false;

        $sourceUtf8 = mb_convert_encoding($html, 'html-entities', 'utf-8');

        @$dom->loadHTML($sourceUtf8, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     * @return string[]
     */
    public function extractAHrefs(DOMDocument $dom): iterable
    {
        $nodeList = $dom->getElementsByTagName('a');

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            yield $node->getAttribute('href');
        }
    }

    public function extractImgSrcs(DOMDocument $dom): iterable
    {
        $nodeList = $dom->getElementsByTagName('img');

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            yield $node->getAttribute('src');
        }
    }
}
