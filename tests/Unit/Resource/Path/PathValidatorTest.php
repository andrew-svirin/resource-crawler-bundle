<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

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
      ['https://site-1.com/', 'https://site-1.com/index.html', true],
      ['https://site-1.com/', '//site.com', true],
      ['https://site-1.com/', '//site.com:80', true],
      ['https://site-1.com/', 'mailto:any@email.com', false],
      ['https://site-1.com/', 'tel:+12345678', false],
      ['https://site-1.com/', 'javascript:do()', false],
      ['https://site-1.com/', 'file://some/file.it', false],
      ['https://site-1.com/', 'https://site-1.com/index.html?q=123#abc(aa)+*;=&$1', false],
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
      ['/level-1/index.html', 'page-1.html', true],
      ['/level-1/index.html', '/page-1.html', false],
      ['/level-1/index.html', 'https://pages/page-1.html', false],
      ['/level-1/index.html', 'page-科.html', false],
    ];
  }
}
