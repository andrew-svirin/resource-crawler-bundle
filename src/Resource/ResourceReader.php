<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reader for resource.
 *
 * @interal
 */
final class ResourceReader
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function read(UriInterface $uri): string
    {
        if ($uri instanceof HttpUri) {
            return $this->readByHttp($uri);
        } else {
            throw new LogicException('URI reader missing');
        }
    }

    private function readByHttp(HttpUri $uri): string
    {
        $response = $this->httpClient->request('GET', $uri->getPath());

        return $response->getContent();
    }
}
