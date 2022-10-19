<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Reader\ResourceReader;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpResource;

final class ResourceCrawler
{

    public function __construct(private readonly ResourceReader $reader)
    {
    }

    /**
     * Walk other nodes graph.
     */
    public function crawl(ResourceInterface $resource)
    {
        $html = $this->reader->read($resource);
        dd('Crawl', $resource);
        // TODO: Implement crawl() method.
    }

    /**
     * Create web resource.
     */
    public function createHttpResource(string $url): HttpResource
    {
        return new HttpResource($url);
    }

    private function readNodeChildren()
    {
    }
}
