<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use MonterHealth\ApiFilterBundle\Tests\Application\Kernel;
use MonterHealth\ApiFilterBundle\Tests\Application\TestDatabaseBootstrap;
use Doctrine\Persistence\ManagerRegistry;

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

/** @var ManagerRegistry $managerRegistry */
$managerRegistry = $kernel->getContainer()->get('doctrine');

/** @var EntityManagerInterface $entityManager */
$entityManager = $managerRegistry->getManager();
TestDatabaseBootstrap::resetAndSeed($entityManager);

$kernel->shutdown();

