<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref;

/**
 * Reference handler closure interface.
 */
interface RefHandlerClosureInterface
{
  public function call(RefPath $refPath): void;
}
