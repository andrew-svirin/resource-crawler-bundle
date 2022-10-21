<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use DOMDocument;

/**
 * HTML node.
 *
 * @interal
 */
final class HtmlNode extends Node
{
    private DOMDocument $document;

    public function setDocument(DOMDocument $document)
    {
        $this->document = $document;
    }

    public function getDocument(): DOMDocument
    {
        return $this->document;
    }
}
