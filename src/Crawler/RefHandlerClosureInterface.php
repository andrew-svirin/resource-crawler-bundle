<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Crawler;

use AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;

/**
 * Reference handler closure interface.
 */
interface RefHandlerClosureInterface
{
  public function call(RefPath $refPath, CrawlingTask $task): void;
}
