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
   */
  public function create(string $content, int $code): Response
  {
    return new Response($content, $code);
  }
}
