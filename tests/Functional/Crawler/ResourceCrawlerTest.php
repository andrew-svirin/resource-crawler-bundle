<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Functional\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\RefHandlerClosureInterface;
use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits\HttpClientTrait;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;
use Closure;
use DOMElement;

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

  /**
   * @return array<array<string | string[] | null>>
   */
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
          'https://site.com/#anchor',
          'https://other-site-2.com/',
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
      ['https://other-site-2.com/', 'ignored', []],
      ['https://site.com/#anchor', 'processed', []],
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

  /**
   * @return array<array<string | string[] | null>>
   */
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

    $resourceCrawler->resetWebResource($url);

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

    $resourceCrawler->resetDiskResource($path);

    $analyze = $resourceCrawler->analyzeCrawlingDiskResource($path);

    $this->assertArrayHasKey('for_processing', $analyze->getStatusCounts());
    $this->assertArrayHasKey('in_process', $analyze->getStatusCounts());
    $this->assertArrayHasKey('processed', $analyze->getStatusCounts());
    $this->assertArrayHasKey('ignored', $analyze->getStatusCounts());
    $this->assertArrayHasKey('errored', $analyze->getStatusCounts());
  }

  public function testWalkTaskNode(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url       = 'https://site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];

    $resourceCrawler->resetWebResource($url);

    $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

    $nodeCalls = $this->walkTaskNodeCalls();
    $i         = 0;

    $callable = function (DOMElement $ref, bool $isValidPath, ?string $normalizedPath, ?bool $isPerformablePath)
    use (&$i, $nodeCalls) {
      $this->assertEquals($nodeCalls[$i][0], $ref->nodeName);
      $this->assertEquals($nodeCalls[$i][1], $isValidPath);
      $this->assertEquals($nodeCalls[$i][2], $normalizedPath);
      $this->assertEquals($nodeCalls[$i][3], $isPerformablePath);

      $i++;
    };

    $op = new class($this, $callable) implements RefHandlerClosureInterface {

      private Closure $closure;

      public function __construct(private readonly ResourceCrawlerTest $newThis, callable $callable)
      {
        $this->closure = $callable(...);
      }

      public function call(DOMElement $ref, bool $isValidPath, ?string $normalizedPath, ?bool $isPerformablePath): void
      {
        $this->closure->call($this->newThis, $ref, $isValidPath, $normalizedPath, $isPerformablePath);
      }
    };

    $resourceCrawler->walkTaskNode($task, $op);
  }

  /**
   * @return array<array<string | string[] | null>>
   */
  private function walkTaskNodeCalls(): array
  {
    return [
      [
        'a',
        true,
        'https://site.com/index.html',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/index.html',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/pages/page-1.html',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/pages/page-2.html',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/pages/page-400',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/pages/page-500',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/pages/page-2.html',
        true,
      ],
      [
        'a',
        true,
        'https://site.com/#anchor',
        true,
      ],
      [
        'a',
        true,
        'https://other-site-2.com/',
        false,
      ],
      [
        'img',
        true,
        'https://site.com/images/img-1.jpg',
        true,
      ],
      [
        'img',
        true,
        'https://site.com/images/img-2.jpg',
        true,
      ],
      [
        'img',
        true,
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        true,
      ],
    ];
  }

  public function testRollbackTask(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url       = 'https://site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];

    $resourceCrawler->resetWebResource($url);

    $analyze1 = $resourceCrawler->analyzeCrawlingWebResource($url);

    $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

    $analyze2 = $resourceCrawler->analyzeCrawlingWebResource($url);

    $resourceCrawler->rollbackTask($task);

    $analyze3 = $resourceCrawler->analyzeCrawlingWebResource($url);

    $this->assertNotEquals($analyze1, $analyze2);
    $this->assertEquals($analyze1, $analyze3);
  }
}
