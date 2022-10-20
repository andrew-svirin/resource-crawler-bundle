<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Reader;

use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\UriInterface;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reader for resource.
 *
 * @interal
 */
final class ResourceReader
{
    public function __construct(private readonly HttpClientInterface $httpClient, private Filesystem $filesystem)
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
