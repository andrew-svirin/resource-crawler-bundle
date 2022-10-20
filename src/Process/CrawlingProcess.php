<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

/**
 * Model implements crawling process.
 *
 * @interal
 */
final class CrawlingProcess
{
    public function __construct(private readonly string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
