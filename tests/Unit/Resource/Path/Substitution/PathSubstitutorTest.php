<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Unit\Resource\Path\Substitution;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutionCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutor;
use AndrewSvirin\ResourceCrawlerBundle\Tests\TestCase;

/**
 * PathRegexMatcherTest
 *
 * @covers \AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutor
 */
class PathSubstitutorTest extends TestCase
{
  /**
   * @dataProvider substituteProvider
   *
   * @param array<array<string>> $subRules
   */
  public function testSubstitute(array $subRules, string $path, string $subPath): void
  {
    /** @var PathSubstitutor $substitutor */
    $substitutor = $this->getContainer()->get(PathSubstitutor::class);
    /** @var PathSubstitutionCreator $creator */
    $creator = $this->getContainer()->get(PathSubstitutionCreator::class);

    $pathSubstitution = $creator->create($subRules);

    $this->assertEquals($subPath, $substitutor->substitute($pathSubstitution, $path));
  }

  /**
   * @return non-empty-array<array>
   */
  public function substituteProvider(): array
  {
    return [
      [
        [['/(pages\?.*)(p=(.[^&#]*))(.*)$/i', '$1page=${3}00${4}']],
        'https://site.com/pages?p=1',
        'https://site.com/pages?page=100',
      ],
      [
        [['/(pages\?.*)(p=(.[^&#]*))(.*)$/i', '$1page=${3}00${4}']],
        'https://site.com/pages?g=1&p=1',
        'https://site.com/pages?g=1&page=100',
      ],
      [
        [['/(pages\?.*)(p=(.[^&#]*))(.*)$/i', '$1page=${3}00${4}']],
        'https://site.com/pages?p=1#a',
        'https://site.com/pages?page=100#a',
      ],
      [
        [['/(\?.*)(w=.[^&#]*)(.*)/i', '$1$3']],
        'https://site.com/pages?w=1',
        'https://site.com/pages?',
      ],
      [
        [['/(\?.*)(w=.[^&#]*)(.*)/i', '$1$3']],
        'https://site.com/pages?g=1&w=1',
        'https://site.com/pages?g=1&',
      ],
      [
        [['/(\?.*)(w=.[^&#]*)(.*)/i', '$1$3']],
        'https://site.com/pages?w=1&g=1',
        'https://site.com/pages?&g=1',
      ],
    ];
  }
}
