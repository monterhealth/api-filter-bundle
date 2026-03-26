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
        // Seed the DB without createClient(): WebTestCase forbids booting the kernel
        // before a second createClient() in the same test method lifecycle.
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        TestDatabaseBootstrap::resetAndSeed($entityManager);
        static::ensureKernelShutdown();
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
        // Multiple range constraints on one field need the [] form (see bundle README).
        $client->request('GET', '/books?pages[][gte]=300&pages[][lte]=350');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(11, $payload);
        foreach ($payload as $book) {
            self::assertGreaterThanOrEqual(300, $book['pages']);
            self::assertLessThanOrEqual(350, $book['pages']);
        }
    }

    public function testGroupedFiltersAndConstraintOnSameField(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books-grouped?mh_groups[0][title][partial]=Harry&mh_groups[1][title][partial]=Potter');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
    }

    public function testGroupedFiltersNarrowsIntersection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books-grouped?mh_groups[0][title][partial]=Harry&mh_groups[1][pages][gte]=300');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $payload);
        self::assertSame('Harry Potter and the Chamber of Secrets', $payload[0]['title']);
        self::assertSame(341, $payload[0]['pages']);
    }

    public function testGroupedEndpointUsesGlobalsForOrder(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/books-grouped?mh_groups[0][title][partial]=Harry&order[desc]=pages'
        );

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
        self::assertSame(341, $payload[0]['pages']);
        self::assertSame(223, $payload[1]['pages']);
    }

    public function testGroupedEndpointSkipsEmptyGroupSlot(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books-grouped?mh_groups[0][title][partial]=Harry&mh_groups[1][ignored][notafield]=x');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
    }

    public function testGroupedEndpointWithoutMhGroupsMatchesFlatFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books-grouped?title[partial]=Harry');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
    }

    public function testOrderFilterUsesDirectionFromQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?order[desc]=pages');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(24, $payload);
        self::assertSame(352, $payload[0]['pages']);
        self::assertSame(112, $payload[23]['pages']);
    }
}
