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

  public function testResetWebResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url = 'https://site.com/index.html';

    $resourceCrawler->resetWebResource($url);

    $this->assertTrue(true);
  }

  public function testCrawlWebResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url       = 'https://site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];

    $resourceCrawler->resetWebResource($url);

    $expectedPaths = [
      ['https://site.com/index.html', 'processed'],
      ['https://site.com/images/img-2.jpg', 'processed'],
      ['https://site.com/images/img-1.jpg', 'processed'],
      ['https://site.com/pages/page-500', 'errored'],
      ['https://site.com/pages/page-400', 'errored'],
      ['https://site.com/pages/page-2.html', 'processed'],
      ['https://site.com/pages/page-1.html', 'processed'],
      ['https://site.com/embed/frame.html', 'ignored'],
      ['https://site-2.com/pages/page-3.html', 'ignored'],
      ['https://site.com/', 'processed'],
      [null, null],
    ];

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
    }
  }

  public function testResetDiskResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $path = $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/index.html';

    $resourceCrawler->resetDiskResource($path);

    $this->assertTrue(true);
  }

  public function testCrawlDiskResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $path      = $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];

    $resourceCrawler->resetDiskResource($path);

    $expectedPaths = [
      ['/var/www/resource-crawler-bundle/tests/Fixtures/resources/filesystem/site.com/index.html', 'processed'],
      ['/var/www/resource-crawler-bundle/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg', 'processed'],
      ['/var/www/resource-crawler-bundle/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg', 'processed'],
      ['/var/www/resource-crawler-bundle/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html', 'processed'],
      ['/var/www/resource-crawler-bundle/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html', 'processed'],
      [null, null],
    ];

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlDiskResource($path, $pathMasks);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
    }
  }
}
