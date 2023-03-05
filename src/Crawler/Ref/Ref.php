<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref;

use DOMElement;

/**
 * Entity for walking ref path.
 */
final class Ref
{
  private bool    $isValid;

  private ?string $normalizedPath = null;

  private ?bool   $isPerformable  = null;

  public function __construct(private readonly DOMElement $element)
  {
  }

  public function getElement(): DOMElement
  {
    return $this->element;
  }

  public function isValid(): bool
  {
    return $this->isValid;
  }

  public function setValid(bool $isValid): void
  {
    $this->isValid = $isValid;
  }

  public function getNormalizedPath(): ?string
  {
    return $this->normalizedPath;
  }

  public function setNormalizedPath(string $normalizedPath): void
  {
    $this->normalizedPath = $normalizedPath;
  }

  public function isPerformable(): ?bool
  {
    return $this->isPerformable;
  }

  public function setPerformable(bool $isPerformable): void
  {
    $this->isPerformable = $isPerformable;
  }
}
