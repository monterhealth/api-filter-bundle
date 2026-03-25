<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilterBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new MonterHealthApiFilterBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    protected function configureContainer(ContainerBuilder $container): void
    {
        $container->loadFromExtension('framework', [
            'secret' => 'api-filter-bundle-test',
            'test' => true,
            'router' => ['utf8' => true],
            'http_method_override' => false,
            'handle_all_throwables' => true,
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'url' => '%env(resolve:DATABASE_URL)%',
                'use_savepoints' => true,
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'enable_lazy_ghost_objects' => true,
                'mappings' => [
                    'Sandbox' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Application/Entity',
                        'prefix' => 'MonterHealth\ApiFilterBundle\Tests\Application\Entity',
                    ],
                ],
            ],
        ]);

        $container->autowire(\MonterHealth\ApiFilterBundle\Tests\Application\Controller\BookController::class)
            ->setPublic(true)
            ->setAutowired(true)
            ->setAutoconfigured(true);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/Controller/', 'attribute');
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
