<?php

namespace MonterHealth\ApiFilterBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MonterHealthApiFilterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // add config to main service
        $definition = $container->getDefinition('monter_health_api_filter.monter_health_api_filter');
        $definition->addMethodCall('setConfigs', [$config]);

        // handle option to override the default ParameterCollectionFactory
        if(null !== $config['parameter_collection_factory']) {
            $container->setAlias('monter_health_api_filter.parameter_collection_factory', $config['parameter_collection_factory']);
        }

        // handle option to override the default Attribute Reader
        if(null !== $config['attribute_reader']) {
            $container->setAlias('monter_health_api_filter.attribute_reader', $config['attribute_reader']);
        }

        // handle option to override the default ApiFilterFactory
        if(null !== $config['api_filter_factory']) {
            $container->setAlias('monter_health_api_filter.api_filter_factory', $config['api_filter_factory']);
        }
    }
}