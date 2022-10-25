<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Node;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;

/**
 * Factory for node.
 *
 * @interal
 */
final class NodeFactory
{
    /**
     * Create HTML node.
     */
    public function createHtml(UriInterface $uri): HtmlNode
    {
        return new HtmlNode($uri);
    }

    /**
     * Create Image node.
     */
    public function createImg(UriInterface $uri): ImgNode
    {
        return new ImgNode($uri);
    }
}
