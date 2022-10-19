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
            ->scalarNode('saver')->end()
            ->end()
            ->end() // process
            ->arrayNode('crawler')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('http_client_mocked')->defaultNull()->info('Use mocker for http_client.')->end()
            ->end()
            ->end() // crawler
            ->end();

        return $treeBuilder;
    }
}
