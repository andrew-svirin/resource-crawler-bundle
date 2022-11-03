<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Node;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;

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
