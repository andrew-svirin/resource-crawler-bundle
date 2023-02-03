<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathComposer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathValidatorTest
 *
 * @covers \AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator
 */
class PathValidatorTest extends TestCase
{
  /**
   * @dataProvider isValidHttpProvider
   */
  public function testIsValidHttp(string $parentPathStr, string $childPathStr, bool $isValid): void
  {
    /** @var UriFactory $uriFactory */
    $uriFactory    = $this->getContainer()->get(UriFactory::class);
    /** @var PathComposer $pathComposer */
    $pathComposer  = $this->getContainer()->get(PathComposer::class);
    /** @var PathValidator $pathValidator */
    $pathValidator = $this->getContainer()->get(PathValidator::class);

    $parentUri = $uriFactory->createHttp($parentPathStr);
    $childPath = $pathComposer->decompose($childPathStr);

    $this->assertEquals($isValid, $pathValidator->isValid($parentUri, $childPath));
  }

  /**
   * @return non-empty-array<array>
   */
  public function isValidHttpProvider(): array
  {
    return [
      ['https://site-1.com/', 'page-'. chr(1), false],
      ['https://site-1.com/', 'page-科', true],
      ['https://site-1.com/', 'page-абв', true],
      ['https://site-1.com/', 'page-äöü', true],
      ['https://site-1.com/', 'https://site-1.com/index.html', true],
      ['https://site-1.com/', '//site.com', true],
      ['https://site-1.com/', '//site.com:80', true],
      ['https://site-1.com/', 'mailto:any@email.com', false],
      ['https://site-1.com/', 'tel:+12345678', false],
      ['https://site-1.com/', 'javascript:do()', false],
      ['https://site-1.com/', 'file://some/file.it', false],
      ['https://site-1.com/', 'https://site-1.com/index.html?q=123#abc(aa)+*;=&$1', true],
      ['https://site-1.com/', 'https://site-1.com/ind ex.html', true],
      ['https://site-1.com/', 'https://site-1.com/ind%20ex.html', true],
    ];
  }

  /**
   * @dataProvider isValidFsProvider
   */
  public function testIsValidFs(string $parentPathStr, string $childPathStr, bool $isValid): void
  {
    /** @var UriFactory $uriFactory */
    $uriFactory    = $this->getContainer()->get(UriFactory::class);
    /** @var PathComposer $pathComposer */
    $pathComposer  = $this->getContainer()->get(PathComposer::class);
    /** @var PathValidator $pathValidator */
    $pathValidator = $this->getContainer()->get(PathValidator::class);

    $parentUri = $uriFactory->createFs($parentPathStr);
    $childPath = $pathComposer->decompose($childPathStr);

    $this->assertEquals($isValid, $pathValidator->isValid($parentUri, $childPath));
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
      ['/level-1/index.html', 'page-科.html', true],
    ];
  }
}
