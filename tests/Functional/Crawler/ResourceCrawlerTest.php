<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Functional\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits\HttpClientTrait;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * ResourceCrawlerTest
 */
class ResourceCrawlerTest extends TestCase
{
    use HttpClientTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupHttpClient();
    }

    public function testWebResourceCrawl()
    {
        /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
        $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

        $resourceCrawler->crawlHttpResource('http://site.com/index.html', [
            '+site.com/',
            '-embed',
        ]);
    }
}
