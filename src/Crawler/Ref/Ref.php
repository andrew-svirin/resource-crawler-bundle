<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref;

use DOMElement;

/**
 * Entity for walking ref path.
 */
final class Ref
{
  public function __construct(private readonly DOMElement $element)
  {
  }

  public function getElement(): DOMElement
  {
    return $this->element;
  }
}
