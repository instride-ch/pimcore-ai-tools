<?php

namespace Instride\Bundle\PimcoreAiToolsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pimcore_ai_tools');
        $rootNode = $treeBuilder->getRootNode();

        $this->addProvidersSection($rootNode);
        $this->addEditableSection($rootNode);
        $this->addFrontendSection($rootNode);

        return $treeBuilder;
    }

    private function addProvidersSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('open_ai')
                            ->children()
                                ->scalarNode('api_key')->end()
                            ->end()
                        ->end()
                        ->arrayNode('midjourney')
                            ->children()
                                ->scalarNode('channel_id')->end()
                                ->scalarNode('auth_key')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addEditableSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('editables')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addFrontendSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('frontend')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;
    }
}
