<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/16/2018
 * Time: 10:20 PM
 */

namespace Monter\ApiFilterBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('monter_api_filter');

        $rootNode
            ->children()
                ->scalarNode('order_parameter_name')->defaultValue('order')->info('The name of the query parameter to order results.')->end()
                ->enumNode('default_order_strategy')->values(['ascending', 'descending'])->defaultValue('ascending')->info('The default order strategy (ascending or descending).')->end()
                ->scalarNode('parameter_collection_factory')->defaultNull()->info('Possibility to override the default ParameterCollectionFactory.')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}