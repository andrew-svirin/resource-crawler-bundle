<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * HTTP resource.
 */
class HttpResource implements ResourceInterface
{
    public function __construct(private $url)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): Node
    {
        return new HtmlNode(new HttpUri($this->url));
    }
}
