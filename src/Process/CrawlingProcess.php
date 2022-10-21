<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;

/**
 * Model implements crawling process.
 *
 * @interal
 */
final class CrawlingProcess
{
    public function __construct(private readonly string $id, private readonly ResourceInterface $resource)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }
}
