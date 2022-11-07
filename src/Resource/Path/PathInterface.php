<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

/**
 * Path interface.
 *
 * @interal
 */
interface PathInterface
{
  public const SCHEME_HTTP = 'http';

  public const SCHEME_HTTPS = 'https';

  public const SCHEME_DATA = 'data';

  public const ALL_SCHEMES = [
    self::SCHEME_HTTP,
    self::SCHEME_HTTPS,
    self::SCHEME_DATA,
  ];

  public function getOriginalPath(): string;

  public function getScheme(): ?string;

  public function getHost(): ?string;

  public function getPath(): ?string;

  public function getQuery(): ?string;

  public function getFragment(): ?string;

  public function isRoot(): bool;

  public function isAbsolute(): bool;
}
