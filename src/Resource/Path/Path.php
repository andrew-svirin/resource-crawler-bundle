<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * Path class.
 *
 * @interal
 */
final class Path implements PathInterface
{
  public function __construct(
    private readonly string $originalPath,
    private readonly ?string $scheme = null,
    private readonly ?string $host = null,
    private readonly ?string $path = null,
    private readonly ?string $query = null,
    private readonly ?string $fragment = null
  ) {
  }

  public function getOriginalPath(): string
  {
    return $this->originalPath;
  }

  public function getScheme(): ?string
  {
    return $this->scheme;
  }

  public function getHost(): ?string
  {
    return $this->host;
  }

  public function getPath(): ?string
  {
    return $this->path;
  }

  public function getQuery(): ?string
  {
    return $this->query;
  }

  public function getFragment(): ?string
  {
    return $this->fragment;
  }

  public function isRoot(): bool
  {
    return empty($this->path) || str_starts_with($this->path, '/');
  }

  public function isAbsolute(): bool
  {
    return !empty($this->host);
  }
}
