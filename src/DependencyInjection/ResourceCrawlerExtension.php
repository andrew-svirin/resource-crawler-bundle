<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection;

use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStoreInterface;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

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

    $this->loadCrawlerHttpClientMocker($config, $container);
    $this->loadProcessStore($config, $container);
    $this->loadProcessFileStore($config, $container);
  }

  /**
   * @param array<string, array<string, array<string, string>>> $config
   */
  private function loadCrawlerHttpClientMocker(array $config, ContainerBuilder $container): void
  {
    if ($config['crawler']['http_client']['mocker']) {
      $container
        ->getDefinition('resource_crawler.reader')
        ->setArgument(0, new Reference($config['crawler']['http_client']['mocker']));
    }
  }

  /**
   * @param array<string, array<string, string>> $config
   */
  private function loadProcessStore(array $config, ContainerBuilder $container): void
  {
    $container->setAlias(ProcessStoreInterface::class, new Reference($config['process']['store']));
  }

  /**
   * @param array<string, array<string, array<string, string>|string>> $config
   */
  private function loadProcessFileStore(array $config, ContainerBuilder $container): void
  {
    if ('resource_crawler.process_file_store' === $config['process']['store']) {
      $definition = $container->getDefinition('resource_crawler.process_file_store');

      $fileStoreDir = $config['process']['file_store']['dir'] ?? sys_get_temp_dir();
      $fileStoreDir .= '/file_store';
      $isLockable   = $config['process']['is_lockable'] ?? false;

      $definition->setArgument(0, $fileStoreDir)->setArgument(1, $isLockable);

      if ($isLockable) {
        $this->setupLockStoreDefinition($fileStoreDir, $container);
        $this->setupLockFactoryDefinition($container);

        $definition->setArgument(4, new Reference('resource_crawler.lock_factory'));
      }
    } elseif ('resource_crawler.process_db_store' === $config['process']['store']) {
      $definition = $container->getDefinition('resource_crawler.process_db_store');

      $fileStoreDir = $config['process']['file_store']['dir'] ?? sys_get_temp_dir();
      $fileStoreDir .= '/file_store';
      $isLockable   = $config['process']['is_lockable'] ?? false;

      $definition->setArgument(1, $isLockable);

      if ($isLockable) {
        $this->setupLockStoreDefinition($fileStoreDir, $container);
        $this->setupLockFactoryDefinition($container);

        $definition->setArgument(4, new Reference('resource_crawler.lock_factory'));
      }
    } else {
      throw new RuntimeException('Wrong process.store');
    }
  }

  private function setupLockStoreDefinition(string $fileStoreDir, ContainerBuilder $container): void
  {
    $definition = new Definition(FlockStore::class, [$fileStoreDir]);

    $container->setDefinition('resource_crawler.lock_store', $definition);
  }

  private function setupLockFactoryDefinition(ContainerBuilder $container): void
  {
    $definition = new Definition(LockFactory::class, [
      new Reference('resource_crawler.lock_store'),
    ]);

    $container->setDefinition('resource_crawler.lock_factory', $definition);
  }
}
