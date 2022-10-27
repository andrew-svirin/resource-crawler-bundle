<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document\Html;

use DOMDocument;
use DOMElement;

/**
 * Service to extract elements from nodes.
 *
 * @interal
 */
final class HtmlExtractor
{
    /**
     * @return string[]
     */
    public function extractAHrefs(DOMDocument $dom): iterable
    {
        $nodeList = $dom->getElementsByTagName('a');

        /** @var \DOMNode|null $node */
        foreach ($nodeList as $node) {
            if ($node instanceof DOMElement) {
                yield $node->getAttribute('href');
            }
        }
    }

    /**
     * @return string[]
     */
    public function extractImgSrcs(DOMDocument $dom): iterable
    {
        $nodeList = $dom->getElementsByTagName('img');

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            if ($node instanceof DOMElement) {
                yield $node->getAttribute('src');
            }
        }
    }
}
