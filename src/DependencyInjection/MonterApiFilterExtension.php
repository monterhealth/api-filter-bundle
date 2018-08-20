<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/16/2018
 * Time: 8:44 PM
 */

namespace Monter\ApiFilterBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MonterApiFilterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // add config to main service
        $definition = $container->getDefinition('monter_api_filter.monter_api_filter');
        $definition->addMethodCall('setConfigs', [$config]);

        // handle option to override the default ParameterCollectionFactory
        if(null !== $config['parameter_collection_factory']) {
            $container->setAlias('monter_api_filter.parameter_collection_factory', $config['parameter_collection_factory']);
        }

        // handle option to override the default Annotation Reader
        if(null !== $config['annotation_reader']) {
            $container->setAlias('monter_api_filter.annotation_reader', $config['annotation_reader']);
        }
    }
}