<?php

namespace Instride\Bundle\PimcoreAiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pimcore_ai');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('open_ai')
                    ->children()
                        ->scalarNode('api_key')->end()
                    ->end()
                ->end() // open_ai
                ->arrayNode('midjourney')
                    ->children()
                        ->scalarNode('channel_id')->end()
                        ->scalarNode('auth_key')->end()
                    ->end()
                ->end() // midjourney
            ->end()
        ;

        return $treeBuilder;
    }
}
