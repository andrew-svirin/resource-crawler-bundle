<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Functional\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits\HttpClientTrait;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * ResourceCrawlerTest
 *
 * @covers \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler
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

    $expectedPaths = $this->crawlWebResourceExpectedPaths();

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
      $this->assertEquals($expectedPaths[$i][2], $task?->getPushedForProcessingPaths());
    }
  }

  private function crawlWebResourceExpectedPaths(): array
  {
    return [
      [
        'https://site.com/index.html',
        'processed',
        [
          'https://site.com/',
          'https://site.com/pages/page-1.html',
          'https://site.com/pages/page-2.html',
          'https://site.com/pages/page-400',
          'https://site.com/pages/page-500',
          'https://site.com/images/img-1.jpg',
          'https://site.com/images/img-2.jpg',
          'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        ],
      ],
      [
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        'processed',
        [],
      ],
      ['https://site.com/images/img-2.jpg', 'processed', []],
      ['https://site.com/images/img-1.jpg', 'processed', []],
      ['https://site.com/pages/page-500', 'errored', []],
      ['https://site.com/pages/page-400', 'errored', []],
      ['https://site.com/pages/page-2.html', 'processed', []],
      [
        'https://site.com/pages/page-1.html',
        'processed',
        [
          'https://site-2.com/pages/page-3.html',
          'https://site.com/embed/frame.html',
        ],
      ],
      ['https://site.com/embed/frame.html', 'ignored', []],
      ['https://site-2.com/pages/page-3.html', 'ignored', []],
      ['https://site.com/', 'processed', []],
      [null, null, null],
    ];
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

    $expectedPaths = $this->crawlDiskResourceExpectedPaths();

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlDiskResource($path, $pathMasks);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
      $this->assertEquals($expectedPaths[$i][2], $task?->getPushedForProcessingPaths());
    }
  }

  private function crawlDiskResourceExpectedPaths(): array
  {
    return [
      [
        $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/index.html',
        'processed',
        [
          $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
          $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
          $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
          $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
        ],
      ],
      [
        $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
        'processed',
        [],
      ],
      [null, null, null],
    ];
  }

  public function testAnalyzeWebResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url = 'https://site.com/index.html';

    $analyze = $resourceCrawler->analyzeCrawlingWebResource($url);

    $this->assertArrayHasKey('for_processing', $analyze->getStatusCounts());
    $this->assertArrayHasKey('in_process', $analyze->getStatusCounts());
    $this->assertArrayHasKey('processed', $analyze->getStatusCounts());
    $this->assertArrayHasKey('ignored', $analyze->getStatusCounts());
    $this->assertArrayHasKey('errored', $analyze->getStatusCounts());
  }

  public function testAnalyzeDiskResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $path = $this->kernel->getProjectDir() . '/tests/Fixtures/resources/filesystem/site.com/index.html';

    $analyze = $resourceCrawler->analyzeCrawlingDiskResource($path);

    $this->assertArrayHasKey('for_processing', $analyze->getStatusCounts());
    $this->assertArrayHasKey('in_process', $analyze->getStatusCounts());
    $this->assertArrayHasKey('processed', $analyze->getStatusCounts());
    $this->assertArrayHasKey('ignored', $analyze->getStatusCounts());
    $this->assertArrayHasKey('errored', $analyze->getStatusCounts());
  }
}
