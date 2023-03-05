<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref;

use DOMElement;

/**
 * Ref Manager.
 */
final class RefManager
{
  public function createRef(DOMElement $element): Ref
  {
    return new Ref($element);
  }
}
