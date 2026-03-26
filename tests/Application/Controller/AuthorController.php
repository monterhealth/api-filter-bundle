<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Author;
use MonterHealth\ApiFilterBundle\Tests\Application\Entity\Book;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AuthorController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MonterHealthApiFilter $apiFilter,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager
            ->getRepository(Author::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.books', 'books')
            ->addSelect('books');

        $this->apiFilter->addFilterConstraints($queryBuilder, Author::class, $request->query);

        $authors = $queryBuilder
            ->getQuery()
            ->getResult();

        $payload = array_map(
            static fn (Author $author): array => [
                'id' => $author->getId(),
                'name' => $author->getName(),
                        'city' => $author->getAddress()->getCity(),
                'books' => array_map(
                    static fn (Book $book): array => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                        'pages' => $book->getPages(),
                    ],
                    $author->getBooks()->toArray()
                ),
            ],
            $authors
        );

        return new JsonResponse($payload);
    }
}
