<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits;

use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * HttpClientTrait
 */
trait HttpClientTrait
{
  private function getResourcesDir(): string
  {
    return $this->kernel->getProjectDir() . '/tests/Fixtures/resources';
  }

  private function setupHttpClient(): void
  {
    /** @var \Symfony\Component\HttpClient\MockHttpClient $httpClient */
    $httpClient = $this->getContainer()->get('http_client_mocker');

    $responseFactory = function ($method, $url, $options) {
      $responses = [
        'http://site.com/'                  => $this->getMock('/http/site.com/index.html'),
        'http://site.com/index.html'        => $this->getMock('/http/site.com/index.html'),
        'http://site.com/pages/page-1.html' => $this->getMock('/http/site.com/pages/page-1.html'),
        'http://site.com/pages/page-2.html' => $this->getMock('/http/site.com/pages/page-2.html'),
        'http://site.com/images/img-1.jpg'  => $this->getMock('/http/site.com/images/img-1.jpg'),
        'http://site.com/images/img-2.jpg'  => $this->getMock('/http/site.com/images/img-2.jpg'),
      ];

      if (empty($responses[$url])) {
        throw new RuntimeException(sprintf('URL `%s` not mocked.', $url));
      }

      return $responses[$url];
    };

    $httpClient->setResponseFactory($responseFactory);
  }

  private function getMock(string $path): MockResponse
  {
    $resourceDir = $this->getResourcesDir();

    return new MockResponse(file_get_contents($resourceDir . $path));
  }
}
