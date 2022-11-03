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
      ->scalarNode('store')->defaultNull()->info('Use store for process.')->end()
      ->arrayNode('file_store')
      ->children()
      ->scalarNode('dir')->defaultNull()->info('Directory for file store.')->end()
      ->end()
      ->end()
      ->end()
      ->end() // process
      ->arrayNode('crawler')
      ->addDefaultsIfNotSet()
      ->children()
      ->arrayNode('http_client')
      ->addDefaultsIfNotSet()
      ->children()
      ->scalarNode('mocker')->defaultNull()->info('Use mocker for http_client.')->end()
      ->end()
      ->end()
      ->end() // crawler
      ->end();

    return $treeBuilder;
  }
}
