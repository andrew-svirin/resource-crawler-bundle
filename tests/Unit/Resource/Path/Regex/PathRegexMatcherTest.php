<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path\Regex;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathRegexMatcherTest
 *
 * @covers \AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexMatcher
 */
class PathRegexMatcherTest extends TestCase
{
  /**
   * @dataProvider isMatchingProvider
   */
  public function testIsMatching(string $path, bool $isMatching): void
  {
    /** @var PathRegexMatcher $matcher */
    $matcher = $this->getContainer()->get(PathRegexMatcher::class);
    /** @var PathRegexCreator $creator */
    $creator = $this->getContainer()->get(PathRegexCreator::class);

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
