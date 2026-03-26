<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Author;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Address;
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
            ['Harry Potter and the Chamber of Secrets', 'J.K. Rowling', 341],
            ['The Murder of Roger Ackroyd', 'Agatha Christie', 352],
            ['Harry Potter and the Philosopher Stone', 'J.K. Rowling', 223],
            ['MonterHealth API Design', 'Niels Geerts', 112],
            ['Clean Architecture', 'Robert C. Martin', 320],
            ['Domain-Driven Design', 'Eric Evans', 330],
            ['Refactoring', 'Martin Fowler', 280],
            ['Patterns of Enterprise Application Architecture', 'Martin Fowler', 290],
            ['Designing Data-Intensive Applications', 'Martin Kleppmann', 336],
            ['The Pragmatic Programmer', 'Andrew Hunt', 320],
            ['Working Effectively with Legacy Code', 'Michael Feathers', 304],
            ['API Security in Action', 'Neil Madden', 288],
            ['Building Microservices', 'Sam Newman', 281],
            ['Release It!', 'Michael T. Nygard', 300],
            ['Accelerate', 'Nicole Forsgren', 245],
            ['The Phoenix Project', 'Gene Kim', 265],
            ['Continuous Delivery', 'Jez Humble', 331],
            ['Test-Driven Development', 'Kent Beck', 220],
            ['Effective Java', 'Joshua Bloch', 310],
            ['Clean Code', 'Robert C. Martin', 264],
            ['The Clean Coder', 'Robert C. Martin', 256],
            ['Software Architecture: The Hard Parts', 'Neal Ford', 296],
            ['Implementing Domain-Driven Design', 'Vaughn Vernon', 340],
            ['Fundamentals of Data Engineering', 'Joe Reis', 318],
        ];

        $authorCities = [
            'J.K. Rowling' => 'Rome',
            'Agatha Christie' => 'Rome',
            'Martin Kleppmann' => 'Rome',
        ];

        $authors = [];
        foreach ($seedBooks as [, $authorName]) {
            if (isset($authors[$authorName])) {
                continue;
            }
            $city = $authorCities[$authorName] ?? 'London';
            $author = new Author($authorName, new Address($city));
            $authors[$authorName] = $author;
            $entityManager->persist($author);
        }

        foreach ($seedBooks as [$title, $authorName, $pages]) {
            $entityManager->persist(new Book($title, $authors[$authorName], $pages));
        }

        $entityManager->flush();
        $entityManager->clear();
    }
}
