<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\FilterGroupsQueryParser;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Book;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class BookController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MonterHealthApiFilter $apiFilter,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager
            ->getRepository(Book::class)
            ->createQueryBuilder('b');

        $split = FilterGroupsQueryParser::splitQueryBag($request->query, $this->apiFilter->getFilterGroupsQueryPrefix());
        if ([] !== $split['groups']) {
            $groups = $split['groups'];
            // Non-group query params become an implicit final AND group.
            if ($split['globals']->count() > 0) {
                $groups[] = $split['globals'];
            }
            $this->apiFilter->addFilterConstraintsGrouped($queryBuilder, Book::class, $groups, $split['globals']);
        } else {
            $this->apiFilter->addFilterConstraints($queryBuilder, Book::class, $request->query);
        }
        $result = $queryBuilder->getQuery()->getResult();

        $payload = array_map(
            static fn (Book $book): array => [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor()->getName(),
                'pages' => $book->getPages(),
            ],
            $result
        );

        return new JsonResponse($payload);
    }

}
