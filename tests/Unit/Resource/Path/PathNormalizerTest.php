<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathComposer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathNormalizerTest
 *
 * @covers \AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer
 */
class PathNormalizerTest extends TestCase
{
  /**
   * @dataProvider normalizeHttpProvider
   */
  public function testNormalizeHttp(string $parentPathStr, string $childPathStr, string $normalizedPath): void
  {
    $uriFactory     = $this->getContainer()->get(UriFactory::class);
    $pathComposer   = $this->getContainer()->get(PathComposer::class);
    $pathNormalizer = $this->getContainer()->get(PathNormalizer::class);

    $parentUri = $uriFactory->createHttp($parentPathStr);
    $childPath = $pathComposer->decompose($childPathStr);

    $this->assertEquals($normalizedPath, $pathNormalizer->normalize($parentUri, $childPath));
  }

  /**
   * @return non-empty-array<array>
   */
  public function normalizeHttpProvider(): array
  {
    return [
      ['https://site-2.com/', '//site-1.com', 'https://site-1.com/'],
      ['https://site-2.com/', 'https://site-1.com/index.html', 'https://site-1.com/index.html'],
      ['https://site-1.com/', 'https://site-1.com/', 'https://site-1.com/'],
      ['https://site-1.com/index.html', '/page-1', 'https://site-1.com/page-1'],
      ['https://site-1.com/pages/index.html', '/page-1.html', 'https://site-1.com/page-1.html'],
      ['https://site-1.com/pages/index.html', '/', 'https://site-1.com/'],
      ['https://site-1.com/index.html', 'page-1.html', 'https://site-1.com/page-1.html'],
      [
        'https://site-1.com/level-1/level-2/level-3/index.html',
        '../page-1.html',
        'https://site-1.com/level-1/level-2/page-1.html',
      ],
      [
        'https://site-1.com/level-1/level-2/level-3/index.html',
        '../../page-1.html',
        'https://site-1.com/level-1/page-1.html',
      ],
      [
        'https://site-1.com/index.html',
        '../../page-1.html',
        'https://site-1.com/page-1.html',
      ],
      [
        'https://site-1.com/index.html',
        '/../../page-1.html',
        'https://site-1.com/page-1.html',
      ],
      [
        'https://site-1.com/index.html',
        './page-1.html',
        'https://site-1.com/page-1.html',
      ],
      [
        'https://site-1.com/level-1/level-2/level-3/index.html',
        './page-1.html',
        'https://site-1.com/level-1/level-2/level-3/page-1.html',
      ],
      [
        'https://site-1.com/level-1/level-2/level-3',
        'level-4',
        'https://site-1.com/level-1/level-2/level-4',
      ],
      [
        'https://site-1.com/level-1/level-2/level-3/',
        'level-4',
        'https://site-1.com/level-1/level-2/level-4',
      ],
      [
        'https://site-1.com/level-1/level-2/level-3?abc=d',
        'level-4?def=g',
        'https://site-1.com/level-1/level-2/level-4?def=g',
      ],
    ];
  }

  /**
   * @dataProvider normalizeFsProvider
   */
  public function testNormalizeFs(string $parentPathStr, string $childPathStr, string $normalizedPath): void
  {
    $uriFactory     = $this->getContainer()->get(UriFactory::class);
    $pathComposer   = $this->getContainer()->get(PathComposer::class);
    $pathNormalizer = $this->getContainer()->get(PathNormalizer::class);

    $parentUri = $uriFactory->createFs($parentPathStr);
    $childPath = $pathComposer->decompose($childPathStr);

    $this->assertEquals($normalizedPath, $pathNormalizer->normalize($parentUri, $childPath));
  }

  /**
   * @return non-empty-array<array>
   */
  public function normalizeFsProvider(): array
  {
    return [
      [
        '/level-1/level-2/level-3/index.html',
        'page-1.html',
        '/level-1/level-2/level-3/page-1.html',
      ],
      [
        '/level-1/level-2/level-3/index.html',
        './page-1.html',
        '/level-1/level-2/level-3/page-1.html',
      ],
      [
        '/level-1/level-2/level-3/index.html',
        '../page-1.html',
        '/level-1/level-2/page-1.html',
      ],
      [
        '/level-1/level-2/level-3/index.html',
        '../../page-1.html',
        '/level-1/page-1.html',
      ],
      [
        '/level-1/level-2/level-3/index.html',
        '.././../page-1.html',
        '/level-1/page-1.html',
      ],
    ];
  }
}
