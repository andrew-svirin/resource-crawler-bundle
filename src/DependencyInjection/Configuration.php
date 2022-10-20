<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('resource_crawler');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('process')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('saver')->defaultNull()->info('Use saver for process.')->end()
            ->arrayNode('file_saver')
            ->children()
            ->scalarNode('dir')->defaultNull()->info('Directory for file saver.')->end()
            ->end()
            ->end()
            ->end()
            ->end() // process
            ->arrayNode('crawler')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('http_client')
            ->children()
            ->scalarNode('mocker')->defaultNull()->info('Use mocker for http_client.')->end()
            ->end()
            ->end()
            ->end() // crawler
            ->end();

        return $treeBuilder;
    }
}
