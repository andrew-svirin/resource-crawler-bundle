<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegex;

/**
 * Filesystem resource.
 *
 * @interal
 */
abstract class Resource implements ResourceInterface
{
  public function __construct(private readonly NodeInterface $node, private readonly ?PathRegex $pathRegex = null)
  {
  }

  public function getRoot(): NodeInterface
  {
    return $this->node;
  }

  public function pathRegex(): ?PathRegex
  {
    return $this->pathRegex;
  }
}
