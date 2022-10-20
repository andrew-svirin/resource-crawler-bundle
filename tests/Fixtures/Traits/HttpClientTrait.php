<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\Traits;

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

        $resourceDir = $this->getResourcesDir();

        $responses = [
            '/index.html'        => new MockResponse(file_get_contents($resourceDir . '/http/index.html')),
            '/pages/page-1.html' => new MockResponse(file_get_contents($resourceDir . '/http/pages/page-1.html')),
            '/pages/page-2.html' => new MockResponse(file_get_contents($resourceDir . '/http/pages/page-2.html')),
            '/images/img-1.jpg'  => new MockResponse(file_get_contents($resourceDir . '/http/images/img-1.jpg')),
            '/images/img-2.jpg'  => new MockResponse(file_get_contents($resourceDir . '/http/images/img-2.jpg')),
        ];

        $httpClient->setResponseFactory($responses);
    }
}
