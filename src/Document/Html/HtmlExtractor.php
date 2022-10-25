<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document\Html;

use DOMDocument;

/**
 * Service to extract elements from nodes.
 *
 * @interal
 */
final class HtmlExtractor
{
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
