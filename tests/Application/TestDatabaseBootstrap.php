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

        $seedBooks = [
            ['Harry Potter and the Chamber of Secrets', 341],
            ['The Murder of Roger Ackroyd', 352],
            ['Harry Potter and the Philosopher Stone', 223],
            ['MonterHealth API Design', 112],
            ['Clean Architecture', 320],
            ['Domain-Driven Design', 330],
            ['Refactoring', 280],
            ['Patterns of Enterprise Application Architecture', 290],
            ['Designing Data-Intensive Applications', 336],
            ['The Pragmatic Programmer', 320],
            ['Working Effectively with Legacy Code', 304],
            ['API Security in Action', 288],
            ['Building Microservices', 281],
            ['Release It!', 300],
            ['Accelerate', 245],
            ['The Phoenix Project', 265],
            ['Continuous Delivery', 331],
            ['Test-Driven Development', 220],
            ['Effective Java', 310],
            ['Clean Code', 264],
            ['The Clean Coder', 256],
            ['Software Architecture: The Hard Parts', 296],
            ['Implementing Domain-Driven Design', 340],
            ['Fundamentals of Data Engineering', 318],
        ];

        foreach ($seedBooks as [$title, $pages]) {
            $entityManager->persist(new Book($title, $pages));
        }

        $entityManager->flush();
        $entityManager->clear();
    }
}
