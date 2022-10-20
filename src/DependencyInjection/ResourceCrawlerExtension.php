<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ResourceCrawlerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
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

        if ($config['process']['saver']) {
            $container
                ->getDefinition('resource_crawler.process_manager')
                ->setArgument(0, new Reference($config['process']['saver']));
        }

        if ($container->hasDefinition('resource_crawler.file_process_saver')) {
            $dir = $config['process']['file_saver']['dir'] ?? sys_get_temp_dir();
            $dir .= '/file_saver';

            $container
                ->getDefinition('resource_crawler.file_process_saver')
                ->setArgument(0, $dir);
        }
    }
}
