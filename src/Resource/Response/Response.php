<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Response;

/**
 * Resource reading Response.
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
