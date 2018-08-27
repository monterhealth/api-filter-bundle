<?php

namespace MonterHealth\ApiFilterBundle\Tests;


use Doctrine\Common\Annotations\Reader;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilterBundle;
use MonterHealth\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new MonterHealthApiFilterTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $factory = $container->get('monter_health_api_filter.parameter_collection_factory');
        $this->assertInstanceOf(ParameterCollectionFactory::class, $factory);

        $reader = $container->get('monter_health_api_filter.annotation_reader');
        $this->assertInstanceOf(Reader::class, $reader);

        $apiFilter = $container->get('monter_health_api_filter.monter_health_api_filter');
        $this->assertInstanceOf(MonterHealthApiFilter::class, $apiFilter);
    }
}

class MonterHealthApiFilterTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new MonterHealthApiFilterBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

}