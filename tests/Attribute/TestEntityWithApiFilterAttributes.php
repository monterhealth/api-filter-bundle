<?php

namespace MonterHealth\ApiFilterBundle\Tests\Attribute;

use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;

/**
 * Class TestEntityWithApiFilterAttributes
 * @package MonterHealth\ApiFilterBundle\Tests
 */

#[ApiFilter(BooleanFilter::class, properties: [ "active" ])]
#[ApiFilter(OrderFilter::class, properties: [ 
    "id",
    "name" => OrderFilter::ASCENDING,
    "active" => OrderFilter::DESCENDING,
])]
class TestEntityWithApiFilterAttributes
{
    private int $id;

    #[ApiFilter(SearchFilter::class)]
    private string $name;

    private bool $active;

    #[ApiFilter(OrderFilter::class, properties: [ OrderFilter::DESCENDING ])]
    private string $notes;
}