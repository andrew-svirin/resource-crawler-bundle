<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegex;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitution;

/**
 * Resource based on graph.
 * End-nodes are files.
 */
interface ResourceInterface
{
  /**
   * Get root node.
   */
  public function getRoot(): NodeInterface;

  /**
   * Path regex for matching.
   */
  public function pathRegex(): ?PathRegex;

  /**
   * Path rules for substitution.
   */
  public function pathSubstitution(): ?PathSubstitution;
}
