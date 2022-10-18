<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Entity\ResourceInterface;

class ResourceCrawler implements ResourceCrawlerInterface
{

    /**
     * {@inheritDoc}
     */
    public function crawl(ResourceInterface $resource)
    {
        dd('Crawl', $resource);
        // TODO: Implement crawl() method.
    }

    private function readNodeChildren()
}
