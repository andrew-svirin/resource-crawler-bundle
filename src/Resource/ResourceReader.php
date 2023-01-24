<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathInterface;
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
    private readonly ResponseFactory $responseFactory,
    private readonly PathExtractor $pathExtractor
  ) {
  }

  public function read(UriInterface $uri): Response
  {
    $scheme = $this->pathExtractor->extractScheme($uri->getPath());

    if (PathInterface::SCHEME_DATA === $scheme) {
      return $this->readData($uri->getPath());
    } elseif ($uri instanceof HttpUri) {
      return $this->readByHttp($uri->getPath());
    } elseif ($uri instanceof FsUri) {
      return $this->readByFs($uri->getPath());
    } else {
      throw new LogicException('URI reader missing');
    }
  }

  private function readData(string $path): Response
  {
    $encodedData = $this->pathExtractor->extractBase64EncodedData($path);

    $content = base64_decode($encodedData);
    $code    = is_string($content) && !empty($content) ? 200 : 600;

    return $this->responseFactory->create($content, $code);
  }

  private function readByHttp(string $path): Response
  {
    try {
      $response = $this->httpClient->request('GET', $path);

      $code = $response->getStatusCode();

      $headers = $response->getHeaders();

      if ($response->getStatusCode() >= 400) {
        $content = 'Response is not correct.';
      } else {
        $content = $response->getContent();
      }
    } catch (Throwable $exception) {
      $content = $exception->getMessage();
      $code    = 600;
    }

    return $this->responseFactory->create($content, $code, $headers ?? null);
  }

  private function readByFs(string $path): Response
  {
    return $this->responseFactory->create(file_get_contents($path), 200);
  }
}
