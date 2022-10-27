<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathNormalizerTest
 */
class PathNormalizerTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalizeHttp(string $parentPath, string $path, string $normalizedPath): void
    {
        $normalizer = new PathNormalizer();
        $uriFactory = new UriFactory();

        $parentUri = $uriFactory->createHttp($parentPath);

        $this->assertEquals($normalizedPath, $normalizer->normalize($parentUri, $path));
    }

    /**
     * @return non-empty-array<array>
     */
    public function normalizeProvider(): array
    {
        return [
            ['http://site-2.com/', '//site-1.com', '//site-1.com/index.html'],
            ['http://site-2.com/', 'http://site-1.com/index.html', 'http://site-1.com/index.html'],
            ['http://site-1.com/', 'http://site-1.com/', 'http://site-1.com/index.html'],
            ['http://site-1.com/index.html', '/page-1', 'http://site-1.com/page-1.html'],
            ['http://site-1.com/pages/index.html', '/page-1.html', 'http://site-1.com/page-1.html'],
            ['http://site-1.com/pages/index.html', '/', 'http://site-1.com/index.html'],
            ['http://site-1.com/index.html', 'page-1.html', 'http://site-1.com/page-1.html'],
            [
                'http://site-1.com/level-1/level-2/level-3/index.html',
                '../page-1.html',
                'http://site-1.com/level-1/level-2/page-1.html',
            ],
            [
                'http://site-1.com/level-1/level-2/level-3/index.html',
                '../../page-1.html',
                'http://site-1.com/level-1/page-1.html',
            ],
            [
                'http://site-1.com/index.html',
                '../../page-1.html',
                'http://site-1.com/page-1.html',
            ],
            [
                'http://site-1.com/index.html',
                '/../../page-1.html',
                'http://site-1.com/page-1.html',
            ],
            [
                'http://site-1.com/index.html',
                './page-1.html',
                'http://site-1.com/page-1.html',
            ],
            [
                'http://site-1.com/level-1/level-2/level-3/index.html',
                './page-1.html',
                'http://site-1.com/level-1/level-2/level-3/page-1.html',
            ],
        ];
    }
}
