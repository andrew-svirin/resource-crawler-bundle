<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * HTTP resource.
 *
 * @interal
 */
final class HttpResource implements ResourceInterface
{
    public function __construct(private $url)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): NodeInterface
    {
        return new HtmlNode(new HttpUri($this->url));
    }
}
