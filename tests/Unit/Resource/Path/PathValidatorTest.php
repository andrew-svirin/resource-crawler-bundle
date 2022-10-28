<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathValidatorTest
 */
class PathValidatorTest extends TestCase
{
    /**
     * @dataProvider isValidHttProvider
     */
    public function testIsValidHttp(string $parentPath, string $path, bool $isValid): void
    {
        $uriFactory = new UriFactory();
        $validator  = new PathValidator();

        $parentUri = $uriFactory->createHttp($parentPath);

        $this->assertEquals($isValid, $validator->isValid($parentUri, $path));
    }

    /**
     * @return non-empty-array<array>
     */
    public function isValidHttProvider(): array
    {
        return [
            ['http://site-1.com/', 'http://site-1.com/index.html', true],
        ];
    }

    /**
     * @dataProvider isValidFsProvider
     */
    public function testIsValidFs(string $parentPath, string $path, bool $isValid): void
    {
        $uriFactory = new UriFactory();
        $validator  = new PathValidator();

        $parentUri = $uriFactory->createFs($parentPath);

        $this->assertEquals($isValid, $validator->isValid($parentUri, $path));
    }

    /**
     * @return non-empty-array<array>
     */
    public function isValidFsProvider(): array
    {
        return [
            ['level-1/index.html', 'page-1.html', true],
        ];
    }
}
