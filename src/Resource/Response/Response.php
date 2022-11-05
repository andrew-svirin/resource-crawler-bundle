<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Response;

/**
 * Response.
 *
 * @interal
 */
final class Response
{
  public function __construct(private readonly string $content, private readonly int $code)
  {
  }

  /**
   * @return string
   */
  public function getContent(): string
  {
    return $this->content;
  }

  /**
   * @return int
   */
  public function getCode(): int
  {
    return $this->code;
  }
}
