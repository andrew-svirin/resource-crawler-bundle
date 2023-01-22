<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;

/**
 * Reference handler closure interface.
 */
interface RefHandlerClosureInterface
{
  public function call(RefPath $refPath): void;
}
