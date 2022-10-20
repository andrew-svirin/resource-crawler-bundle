<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

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
