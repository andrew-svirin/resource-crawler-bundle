<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Response;

/**
 * Factory for response.
 *
 * @interal
 */
final class ResponseFactory
{
  /**
   * Create response.
   * @param string[][]|null $headers
   */
  public function create(string $content, int $code, array $headers = null): Response
  {
    return new Response($content, $code, $headers);
  }
}
