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

    public function testResetHttpResource(): void
    {
        /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
        $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

        $url = 'http://site.com/index.html';

        $resourceCrawler->resetHttpResource($url);

        $this->assertTrue(true);
    }

    public function testCrawlWebResource(): void
    {
        /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
        $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

        $url       = 'http://site.com/index.html';
        $pathMasks = ['+site.com/', '-embed'];

        $resourceCrawler->resetHttpResource($url);

        $expectedPaths = [
            ['http://site.com/index.html', 'processed'],
            ['http://site.com/images/img-2.jpg', 'processed'],
            ['http://site.com/images/img-1.jpg', 'processed'],
            ['http://site.com/pages/page-2.html', 'processed'],
            ['http://site.com/pages/page-1.html', 'processed'],
            ['http://site.com/embed/frame.html', 'ignored'],
            ['http://site-2.com/pages/page-3.html', 'ignored'],
            [null, null],
        ];

        for ($i = 0; $i < count($expectedPaths); $i++) {
            $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

            $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
            $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
        }
    }

    public function testCrawlDiskResource(): void
    {
        // TODO:
    }
}
