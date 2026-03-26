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

    public function testSearchAuthorFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?author:name[partial]=Robert');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(3, $payload);
        foreach ($payload as $book) {
            self::assertStringContainsString('Robert', $book['author']);
        }
    }

    public function testSearchNestedAuthorNameCreatesJoin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?author:name[equals]=Agatha Christie');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $payload);
        self::assertSame('The Murder of Roger Ackroyd', $payload[0]['title']);
        self::assertSame('Agatha Christie', $payload[0]['author']);
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
        $client->request('GET', '/books?mh_groups[0][title][partial]=Harry&mh_groups[1][title][partial]=Potter');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
    }

    public function testGroupedFiltersNarrowsIntersection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?mh_groups[0][title][partial]=Harry&mh_groups[1][pages][gte]=300');

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
            '/books?mh_groups[0][title][partial]=Harry&order[desc]=pages'
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
        $client->request('GET', '/books?mh_groups[0][title][partial]=Harry&mh_groups[1][ignored][notafield]=x');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(2, $payload);
    }

    public function testGroupedEndpointWithoutMhGroupsMatchesFlatFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books?title[partial]=Harry');

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

    public function testAuthorsEndpointReturnsAuthorsWithBookCollections(): void
    {
        $client = static::createClient();
        $client->request('GET', '/authors');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertNotEmpty($payload);

        $jkRowling = null;
        foreach ($payload as $author) {
            if (($author['name'] ?? null) === 'J.K. Rowling') {
                $jkRowling = $author;
                break;
            }
        }

        self::assertNotNull($jkRowling);
        self::assertCount(2, $jkRowling['books']);
    }

    public function testAuthorsFilterByName(): void
    {
        $client = static::createClient();
        $client->request('GET', '/authors?name[partial]=Martin');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(3, $payload);
        foreach ($payload as $author) {
            self::assertStringContainsString('Martin', $author['name']);
        }
    }

    public function testAuthorsFilterByNestedBookTitle(): void
    {
        $client = static::createClient();
        $client->request('GET', '/authors?books:title[partial]=Harry');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $payload);
        self::assertSame('J.K. Rowling', $payload[0]['name']);
    }

    public function testAuthorsFilterByNestedBookPagesRange(): void
    {
        $client = static::createClient();
        $client->request('GET', '/authors?books:pages[gte]=340');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(3, $payload);
        $names = array_column($payload, 'name');
        sort($names);
        self::assertSame(['Agatha Christie', 'J.K. Rowling', 'Vaughn Vernon'], $names);
    }

    public function testAuthorsGroupedFilterWithMhGroupsAndGlobals(): void
    {
        $client = static::createClient();
        $client->request('GET', '/authors?mh_groups[0][name][partial]=andrew&name[partial]=hunt');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent() ?: '[]', true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $payload);
        self::assertSame('Andrew Hunt', $payload[0]['name']);
    }
}
