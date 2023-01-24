<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Response;

/**
 * Resource reading Response.
 */
final class Response
{
  /**
   * @param string[][] | null $headers
   */
  public function __construct(
    private readonly string $content,
    private readonly int $code,
    private readonly ?array $headers = null
  ) {
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

  /**
   * @return string[][] | null
   */
  public function getHeaders(): ?array
  {
    return $this->headers;
  }
}
