<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Book;

final class TestDatabaseBootstrap
{
    public static function resetAndSeed(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $entityManager->persist(new Book('Harry Potter and the Chamber of Secrets', 341));
        $entityManager->persist(new Book('The Murder of Roger Ackroyd', 352));
        $entityManager->persist(new Book('Harry Potter and the Philosopher Stone', 223));
        $entityManager->persist(new Book('MonterHealth API Design', 112));

        $entityManager->flush();
        $entityManager->clear();
    }
}
