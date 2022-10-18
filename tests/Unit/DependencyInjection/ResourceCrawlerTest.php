<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\DependencyInjection;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler;
use AndrewSvirin\ResourceCrawlerBundle\Entity\WebResource;
use PHPUnit\Framework\TestCase;

/**
 * ResourceCrawlerTest
 */
class ResourceCrawlerTest extends TestCase
{

    public function testWebResourceCrawl()
    {
        $resourceCrawler = new ResourceCrawler();
        $resource        = new WebResource('http://site.com');

        $resourceCrawler->crawl($resource);
    }
}
