<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FilterEndpointTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::ensureKernelShutdown();
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        TestDatabaseBootstrap::resetAndSeed($entityManager);
    }

    public function testSearchPartialFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?title[partial]=Harry');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
        self::assertSame('Harry Potter and the Chamber of Secrets', $payload[0]['title']);
        self::assertSame('Harry Potter and the Philosopher Stone', $payload[1]['title']);
    }

    public function testRangeFilterCombinesOperators(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?pages[gte]=300&pages[lte]=350');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $payload);
        self::assertSame('Harry Potter and the Chamber of Secrets', $payload[0]['title']);
    }

    public function testOrderFilterUsesDirectionFromQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?order[desc]=pages');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(4, $payload);
        self::assertSame(352, $payload[0]['pages']);
        self::assertSame(112, $payload[3]['pages']);
    }
}
