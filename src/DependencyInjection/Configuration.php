<?php

namespace MonterHealth\ApiFilterBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('monter_health_api_filter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('order_parameter_name')->defaultValue('order')->info('The name of the query parameter to order results.')->end()
                ->enumNode('default_order_strategy')->values(['ascending', 'descending'])->defaultValue('ascending')->info('The default order strategy (ascending or descending).')->end()
                ->scalarNode('parameter_collection_factory')->defaultNull()->info('Possibility to override the default ParameterCollectionFactory.')->end()
                ->scalarNode('annotation_reader')->defaultNull()->info('Possibility to override the default annotation reader.')->end()
                ->scalarNode('api_filter_factory')->defaultNull()->info('Possibility to override the default ApiFilterFactory.')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}