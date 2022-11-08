<?php

namespace AndrewSvirin\ResourceCrawlerBundle;

use AndrewSvirin\ResourceCrawlerBundle\DependencyInjection\ResourceCrawlerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ResourceCrawlerBundle
 */
final class ResourceCrawlerBundle extends Bundle
{
  public function getContainerExtension(): ?ExtensionInterface
  {
    return new ResourceCrawlerExtension();
  }
}
