<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;
use RuntimeException;
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
        } elseif ($uri instanceof FsUri) {
            return $this->readByFs($uri);
        } else {
            throw new LogicException('URI reader missing');
        }
    }

    private function readByHttp(HttpUri $uri): string
    {
        $response = $this->httpClient->request('GET', $uri->getPath());

        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException('Response is not correct');
        }

        return $response->getContent();
    }

    private function readByFs(FsUri $uri): string
    {
        return file_get_contents($uri->getPath());
    }
}
