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

    public function testResetHttpResource()
    {
        /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
        $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

        $url = 'http://site.com/index.html';

        $resourceCrawler->resetHttpResource($url);
    }

    public function testCrawlHttpResource()
    {
        /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
        $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

        $url = 'http://site.com/index.html';

        $resourceCrawler->resetHttpResource($url);

        $expectedPaths = [
            'http://site.com/index.html',
            'http://site.com/images/img-2.jpg',
            'http://site.com/images/img-1.jpg',
            'http://site.com/pages/page-2.html',
            'http://site.com/pages/page-1.html',
            null,
        ];

        for ($i = 0; $i <= 5; $i++) {
            $task = $resourceCrawler->crawlHttpResource($url, [
                '+site.com/',
                '-embed',
            ]);

            $this->assertEquals($expectedPaths[$i], $task?->getNode()->getUri()->getPath());
        }
    }
}
