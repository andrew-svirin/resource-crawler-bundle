<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Reader;

use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ResourceInterface;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ResourceReader
{
    public function __construct(private readonly HttpClientInterface $httpClient, private Filesystem $filesystem)
    {
    }

    public function read(ResourceInterface $resource): string
    {
        $node = $resource->getRoot();

        $uri = $node->getUri();

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
