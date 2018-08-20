<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/20/2018
 * Time: 9:13 PM
 */

namespace Monter\ApiFilterBundle\Tests;


use Doctrine\Common\Annotations\Reader;
use Monter\ApiFilterBundle\MonterApiFilter;
use Monter\ApiFilterBundle\MonterApiFilterBundle;
use Monter\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new MonterApiFilterTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $factory = $container->get('monter_api_filter.parameter_collection_factory');
        $this->assertInstanceOf(ParameterCollectionFactory::class, $factory);

        $reader = $container->get('monter_api_filter.annotation_reader');
        $this->assertInstanceOf(Reader::class, $reader);

        $apiFilter = $container->get('monter_api_filter.monter_api_filter');
        $this->assertInstanceOf(MonterApiFilter::class, $apiFilter);
    }
}

class MonterApiFilterTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new MonterApiFilterBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

}