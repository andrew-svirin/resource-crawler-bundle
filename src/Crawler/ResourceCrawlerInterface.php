<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Entity\ResourceInterface;

/**
 * Crawler for the resource.
 */
interface ResourceCrawlerInterface
{
    public function crawl(ResourceInterface $resource);
}
