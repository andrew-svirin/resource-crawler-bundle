<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\Ref;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;

/**
 * Reference handler closure interface.
 */
interface RefHandlerClosureInterface
{
  public function call(Ref $ref, CrawlingTask $task): void;
}
