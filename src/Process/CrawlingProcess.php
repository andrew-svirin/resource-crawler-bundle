<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;

/**
 * Model implements crawling process.
 */
final class CrawlingProcess
{
  public function __construct(private readonly string $name, private readonly ResourceInterface $resource)
  {
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getResource(): ResourceInterface
  {
    return $this->resource;
  }
}
