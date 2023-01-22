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
        'https://site.com/'                   => $this->getMock('/http/site.com/index.html'),
        'https://site.com/index.html'         => $this->getMock('/http/site.com/index.html'),
        'https://site.com/#anchor'            => $this->getMock('/http/site.com/index.html'),
        'https://site.com/index.html?a=1&b=1' => $this->getMock('/http/site.com/index.html'),
        'https://site.com/pages/page-1.html'  => $this->getMock('/http/site.com/pages/page-1.html'),
        'https://site.com/pages/page-2.html'  => $this->getMock('/http/site.com/pages/page-2.html'),
        'https://site.com/pages/page-400'     => $this->getMock('/http/site.com/pages/page-400.html', 400),
        'https://site.com/pages/page-500'     => $this->getMock('/http/site.com/pages/page-500.html', 500),
        'https://site.com/images/img-1.jpg'   => $this->getMock('/http/site.com/images/img-1.jpg'),
        'https://site.com/images/img-2.jpg'   => $this->getMock('/http/site.com/images/img-2.jpg'),
      ];

      if (empty($responses[$url])) {
        throw new RuntimeException(sprintf('URL `%s` not mocked.', $url));
      }

      return $responses[$url];
    };

    $httpClient->setResponseFactory($responseFactory);
  }

  private function getMock(string $path, int $code = 200): MockResponse
  {
    $resourceDir = $this->getResourcesDir();

    return new MockResponse(file_get_contents($resourceDir . $path), [
      'http_code' => $code,
    ]);
  }
}
