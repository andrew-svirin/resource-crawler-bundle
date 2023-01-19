<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use DOMElement;

/**
 * Reference handler closure interface.
 */
interface RefHandlerClosureInterface
{
  public function call(DOMElement $ref, bool $isValidPath, ?string $normalizedPath, ?bool $isPerformablePath): void;
}
