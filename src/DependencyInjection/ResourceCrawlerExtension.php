<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ResourceCrawlerExtension extends Extension
{
  /**
   * {@inheritDoc}
   */
  public function load(array $configs, ContainerBuilder $container): void
  {
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config        = $this->processConfiguration($configuration, $configs);

    if ($config['crawler']['http_client']['mocker']) {
      $container
        ->getDefinition('resource_crawler.reader')
        ->setArgument(0, new Reference($config['crawler']['http_client']['mocker']));
    }

    if ($config['process']['store']) {
      $container
        ->getDefinition('resource_crawler.process_manager')
        ->setArgument(0, new Reference($config['process']['store']));
    }

    if ($container->hasDefinition('resource_crawler.process_file_store')) {
      $dir = $config['process']['file_store']['dir'] ?? sys_get_temp_dir();
      $dir .= '/file_store';

      $container
        ->getDefinition('resource_crawler.process_file_store')
        ->setArgument(0, $dir);
    }
  }
}
