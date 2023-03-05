<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Functional\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\Ref;
use AndrewSvirin\ResourceCrawlerBundle\Crawler\RefHandlerClosureInterface;
use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\File\FileProcessStore;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits\HttpClientTrait;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;
use Closure;
use ReflectionProperty;

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

    $url               = 'https://site.com/index.html';
    $pathMasks         = ['+site.com/', '-embed'];
    $substitutionRules = [
      ['/(#other-anchor)/i', ''], // remove anchor `other-anchor`
      ['/(\?.*)([&*]h=[^&#]*)(.*)/i', '$1$3'], // remove query param `h`
      ['/(\?.*)([&*]w=[^&#]*)(.*)/i', '$1$3'], // remove query param `w`
    ];

    $resourceCrawler->resetWebResource($url);

    $pm = $this->getContainer()->get(ProcessManager::class);

    $reflectionProperty = new ReflectionProperty($pm, 'processStore');
    $reflectionProperty->setAccessible(true);

    $ps = $reflectionProperty->getValue($pm);

    if ($ps instanceof FileProcessStore) {
      $expectedPaths = $this->crawlWebResourceFIleExpectedPaths();
    } else {
      $expectedPaths = $this->crawlWebResourceDbExpectedPaths();
    }

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlWebResource($url, $pathMasks, $substitutionRules);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
      $this->assertEquals($expectedPaths[$i][2], $task?->getPushedForProcessingPaths());
    }
  }

  /**
   * @return array<array<string | string[] | null>>
   */
  private function crawlWebResourceFIleExpectedPaths(): array
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
          'https://site.com/images/img-2.jpg',
          'https://site.com/#anchor',
          'https://site.com/index.html?a=1&b=1',
          'https://site.com/images/img-1.jpg',
          'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        ],
      ],
      [
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        'processed',
        [],
      ],
      ['https://site.com/images/img-1.jpg', 'processed', []],
      ['https://site.com/index.html?a=1&b=1', 'processed', []],
      ['https://site.com/#anchor', 'processed', []],
      ['https://site.com/images/img-2.jpg', 'processed', []],
      ['https://site.com/pages/page-500', 'errored', []],
      ['https://site.com/pages/page-400', 'errored', []],
      ['https://site.com/pages/page-2.html', 'processed', []],
      [
        'https://site.com/pages/page-1.html',
        'processed',
        [],
      ],
      ['https://site.com/', 'processed', []],
      [null, null, null],
    ];
  }

  /**
   * @return array<array<string | string[] | null>>
   */
  private function crawlWebResourceDbExpectedPaths(): array
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
          'https://site.com/images/img-2.jpg',
          'https://site.com/#anchor',
          'https://site.com/index.html?a=1&b=1',
          'https://site.com/images/img-1.jpg',
          'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        ],
      ],
      ['https://site.com/', 'processed', []],
      [
        'https://site.com/pages/page-1.html',
        'processed',
        [],
      ],
      ['https://site.com/pages/page-2.html', 'processed', []],
      ['https://site.com/pages/page-400', 'errored', []],
      ['https://site.com/pages/page-500', 'errored', []],
      ['https://site.com/images/img-2.jpg', 'processed', []],
      ['https://site.com/#anchor', 'processed', []],
      ['https://site.com/index.html?a=1&b=1', 'processed', []],
      ['https://site.com/images/img-1.jpg', 'processed', []],
      [
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        'processed',
        [],
      ],
      [null, null, null],
    ];
  }

  public function testResetDiskResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $path = $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/index.html';

    $resourceCrawler->resetDiskResource($path);

    $this->assertTrue(true);
  }

  public function testCrawlDiskResource(): void
  {
    /** @var \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler $resourceCrawler */
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $path       = $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/index.html';
    $pathMasks  = ['+site.com/', '-embed'];
    $substRules = [["/(#.*)/i", ""], ["/(\\?[^#]*)/i", ""]];

    $resourceCrawler->resetDiskResource($path);

    $pm = $this->getContainer()->get(ProcessManager::class);

    $reflectionProperty = new ReflectionProperty($pm, 'processStore');
    $reflectionProperty->setAccessible(true);

    $ps = $reflectionProperty->getValue($pm);

    if ($ps instanceof FileProcessStore) {
      $expectedPaths = $this->crawlDiskResourceFileExpectedPaths();
    } else {
      $expectedPaths = $this->crawlDiskResourceDbExpectedPaths();
    }

    for ($i = 0; $i < count($expectedPaths); $i++) {
      $task = $resourceCrawler->crawlDiskResource($path, $pathMasks, $substRules);

      $this->assertEquals($expectedPaths[$i][0], $task?->getNode()->getUri()->getPath());
      $this->assertEquals($expectedPaths[$i][1], $task?->getStatus());
      $this->assertEquals($expectedPaths[$i][2], $task?->getPushedForProcessingPaths());
    }
  }

  /**
   * @return array<array<string | string[] | null>>
   */
  private function crawlDiskResourceFileExpectedPaths(): array
  {
    return [
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/index.html',
        'processed',
        [
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
        ],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
        'processed',
        [],
      ],
      [null, null, null],
    ];
  }

  /**
   * @return array<array<string | string[] | null>>
   */
  private function crawlDiskResourceDbExpectedPaths(): array
  {
    return [
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/index.html',
        'processed',
        [
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
          $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
        ],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-1.html',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/pages/page-2.html',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-2.jpg',
        'processed',
        [],
      ],
      [
        $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/images/img-1.jpg',
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

    $path = $this->kernel->getProjectDir().'/tests/Fixtures/resources/filesystem/site.com/index.html';

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

    $nodeCalls = $this->walkTaskNodeCalls();
    $i         = 0;

    $callable = function (Ref $ref) use (&$i, $nodeCalls) {
      $this->assertEquals($nodeCalls[$i][0], $ref->getElement()->nodeName);
      $this->assertEquals($nodeCalls[$i][1], $ref->getNormalizedPath());
      $this->assertEquals($nodeCalls[$i][2], $ref->isValid());
      $this->assertEquals($nodeCalls[$i][3], $ref->isPerformable());

      $i++;
    };

    $op = new class($this, $callable) implements RefHandlerClosureInterface {

      private Closure $closure;

      public function __construct(private readonly ResourceCrawlerTest $newThis, callable $callable)
      {
        $this->closure = $callable(...);
      }

      public function call(Ref $ref, CrawlingTask $task): void
      {
        $this->closure->call($this->newThis, $ref, $task);
      }
    };

    $resourceCrawler->crawlWebResource($url, $pathMasks, null, $op);
  }

  /**
   * @return array<int, array<int, bool|string>>.
   */
  private function walkTaskNodeCalls(): array
  {
    return [
      [
        'a',
        'https://site.com/index.html',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/index.html',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/pages/page-1.html',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/pages/page-2.html',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/pages/page-400',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/pages/page-500',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/pages/page-2.html',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/images/img-2.jpg',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/#anchor',
        true,
        true,
      ],
      [
        'a',
        'https://site.com/#other-anchor',
        true,
        true,
      ],
      [
        'a',
        'https://other-site-2.com/',
        true,
        false,
      ],
      [
        'a',
        'https://site.com/index.html?a=1&w=1&h=1&b=1',
        true,
        true,
      ],
      [
        'img',
        'https://site.com/images/img-1.jpg',
        true,
        true,
      ],
      [
        'img',
        'https://site.com/images/img-2.jpg',
        true,
        true,
      ],
      [
        'img',
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==',
        true,
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
