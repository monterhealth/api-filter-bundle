<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilterBundle;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\AuthorController;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\BookController;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\FaviconController;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

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
        // MicroKernelTrait relies on this method for loading extension configs.
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'api-filter-bundle-test',
                'test' => true,
                'router' => [
                    'utf8' => true,
                    // Symfony's FrameworkBundle config requires a router resource.
                    // All sandbox routes are defined in routes.php.
                    'resource' => '%kernel.project_dir%/tests/Application/routes.php',
                ],
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
                            'prefix' => 'MonterHealth\\ApiFilterBundle\\Tests\\Application\\Entity',
                        ],
                    ],
                ],
            ]);

            // Register the sandbox controller explicitly so Symfony can autowire
            // method arguments like EntityManagerInterface and MonterHealthApiFilter.
            $container
                ->register(BookController::class)
                ->setAutowired(true)
                ->setPublic(true)
                ->setAutoconfigured(true);

            $container
                ->register(AuthorController::class)
                ->setAutowired(true)
                ->setPublic(true)
                ->setAutoconfigured(true);

            $container
                ->register(FaviconController::class)
                ->setPublic(true);
        });
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
