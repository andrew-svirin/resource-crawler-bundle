<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Node;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Response\Response;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;

/**
 * Resource node.
 *
 * @interal
 */
interface NodeInterface
{
  public function getUri(): UriInterface;

  public function getResponse(): ?Response;

  public function setResponse(Response $response): void;
}
