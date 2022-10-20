<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Resource node.
 *
 * @interal
 */
interface NodeInterface
{
    public function getUri(): UriInterface;

    public function getContent(): string;

    public function setContent(string $content): void;
}
