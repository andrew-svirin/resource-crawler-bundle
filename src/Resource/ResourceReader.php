<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Response\Response;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Response\ResponseFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Reader for resource.
 *
 * @interal
 */
final class ResourceReader
{
  public function __construct(
    private readonly HttpClientInterface $httpClient,
    private readonly ResponseFactory $responseFactory
  ) {
  }

  public function read(UriInterface $uri): Response
  {
    if ($uri instanceof HttpUri) {
      return $this->readByHttp($uri);
    } elseif ($uri instanceof FsUri) {
      return $this->readByFs($uri);
    } else {
      throw new LogicException('URI reader missing');
    }
  }

  private function readByHttp(HttpUri $uri): Response
  {
    try {
      $response = $this->httpClient->request('GET', $uri->getPath());

      $code = $response->getStatusCode();

      if ($response->getStatusCode() >= 400) {
        $content = 'Response is not correct.';
      } else {
        $content = $response->getContent();
      }
    } catch (Throwable $exception) {
      $content = $exception->getMessage();
      $code    = 600;
    }

    return $this->responseFactory->create($content, $code);
  }

  private function readByFs(FsUri $uri): Response
  {
    return $this->responseFactory->create(file_get_contents($uri->getPath()), 200);
  }
}
