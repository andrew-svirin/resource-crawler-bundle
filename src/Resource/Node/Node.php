<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Node;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Response\Response;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;

/**
 * Resource node.
 *
 * @interal
 */
abstract class Node implements NodeInterface
{
  private ?Response $response = null;

  public function __construct(private readonly UriInterface $uri)
  {
  }

  public function getUri(): UriInterface
  {
    return $this->uri;
  }

  public function getResponse(): ?Response
  {
    return $this->response;
  }

  public function setResponse(Response $response): void
  {
    $this->response = $response;
  }
}
