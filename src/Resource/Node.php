<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Resource node.
 *
 * @interal
 */
abstract class Node implements NodeInterface
{
    private string $content;

    public function __construct(private readonly UriInterface $uri)
    {
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
