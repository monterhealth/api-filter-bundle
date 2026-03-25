<?php

namespace MonterHealth\ApiFilterBundle\Tests;


use MonterHealth\ApiFilterBundle\Attribute\Reader;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use MonterHealth\ApiFilterBundle\Tests\Application\Kernel;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $factory = $container->get('monter_health_api_filter.parameter_collection_factory');
        $this->assertInstanceOf(ParameterCollectionFactory::class, $factory);

        $reader = $container->get('monter_health_api_filter.attribute_reader');
        $this->assertInstanceOf(Reader::class, $reader);

        $apiFilter = $container->get('monter_health_api_filter.monter_health_api_filter');
        $this->assertInstanceOf(MonterHealthApiFilter::class, $apiFilter);

        $kernel->shutdown();
    }
}