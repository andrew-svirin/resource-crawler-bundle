<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathRegexMatcherTest
 */
class PathRegexMatcherTest extends TestCase
{
  /**
   * @dataProvider isMatchingProvider
   */
  public function testIsMatching(string $path, bool $isMatching): void
  {
    $matcher = new PathRegexMatcher();

    $creator = new PathRegexCreator();

    $pathRegex = $creator->create([
      '+site1.com/',
      '-embed',
      '+site2.com/',
      '-page',
    ]);

    $this->assertEquals($isMatching, $matcher->isMatching($pathRegex, $path));
  }

  /**
   * @return non-empty-array<array>
   */
  public function isMatchingProvider(): array
  {
    return [
      ['https://site.com/', false],
      ['https://site1.com/', true],
      ['https://site2.com/', true],
      ['https://site1.com/embed/index.html', false],
      ['https://site2.com/page-1.html', false],
    ];
  }
}
