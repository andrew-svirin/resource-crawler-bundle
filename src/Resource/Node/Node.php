<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Node;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;

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
