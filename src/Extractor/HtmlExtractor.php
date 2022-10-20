<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Extractor;

use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;

/**
 * Service to extract elements from nodes.
 *
 * @interal
 */
final class HtmlExtractor
{
    public function extractLinks(HtmlNode $node): iterable
    {
        return [
            'a',
            'b',
        ];
    }
}
