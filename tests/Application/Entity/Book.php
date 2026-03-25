<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\RangeFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
#[ApiFilter(SearchFilter::class, properties: ['title'])]
#[ApiFilter(RangeFilter::class, properties: ['pages'])]
#[ApiFilter(OrderFilter::class, properties: [
    'title' => OrderFilter::ASCENDING,
    'pages' => OrderFilter::DESCENDING,
])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $pages;

    public function __construct(string $title, int $pages)
    {
        $this->title = $title;
        $this->pages = $pages;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}
