<?php

declare(strict_types=1);

use MonterHealth\ApiFilterBundle\Attribute\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Attribute\Reader;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\DateFilter;
use MonterHealth\ApiFilterBundle\Filter\NumericFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\RangeFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\Factory\DefaultParameterCollectionFactory;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('monter_health_api_filter.default_attribute_reader', Reader::class);
    $services->alias('monter_health_api_filter.attribute_reader', 'monter_health_api_filter.default_attribute_reader')
        ->public();

    $services->set('monter_health_api_filter.default_parameter_collection_factory', DefaultParameterCollectionFactory::class);
    $services->alias('monter_health_api_filter.parameter_collection_factory', 'monter_health_api_filter.default_parameter_collection_factory')
        ->public();

    $services->set('monter_health_api_filter.default_api_filter_factory', ApiFilterFactory::class)
        ->arg('$reader', service('monter_health_api_filter.attribute_reader'));
    $services->alias('monter_health_api_filter.api_filter_factory', 'monter_health_api_filter.default_api_filter_factory')
        ->public();

    $services->set('monter_health_api_filter.query_name_generator', QueryNameGenerator::class);

    $services->set(BooleanFilter::class)
        ->tag('monter_health_api_filter');
    $services->set(SearchFilter::class)
        ->tag('monter_health_api_filter');
    $services->set(OrderFilter::class)
        ->tag('monter_health_api_filter');
    $services->set(NumericFilter::class)
        ->tag('monter_health_api_filter');
    $services->set(RangeFilter::class)
        ->tag('monter_health_api_filter');
    $services->set(DateFilter::class)
        ->tag('monter_health_api_filter');

    $services->alias(MonterHealthApiFilter::class, 'monter_health_api_filter.monter_health_api_filter');

    $services->set('monter_health_api_filter.monter_health_api_filter', MonterHealthApiFilter::class)
        ->public()
        ->arg('$filters', tagged_iterator('monter_health_api_filter'))
        ->arg('$parameterCollectionFactory', service('monter_health_api_filter.parameter_collection_factory'))
        ->arg('$apiFilterFactory', service('monter_health_api_filter.api_filter_factory'))
        ->arg('$queryNameGenerator', service('monter_health_api_filter.query_name_generator'))
        ->arg('$managerRegistry', service('doctrine'));
};
