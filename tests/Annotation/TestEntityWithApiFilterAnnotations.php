<?php

namespace MonterHealth\ApiFilterBundle\Tests\Annotation;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;

/**
 * Class TestEntityWithApiFilterAnnotations
 * @package MonterHealth\ApiFilterBundle\Tests
 * @ApiFilter(BooleanFilter::class, properties={"active"})
 * @ApiFilter(OrderFilter::class, properties={
 *     "id",
 *     "name": OrderFilter::ASCENDING,
 *     "active": OrderFilter::DESCENDING,
 * })
 */
class TestEntityWithApiFilterAnnotations
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @ApiFilter(SearchFilter::class)
     */
    private $name;

    /**
     * @var boolean
     */
    private $active;
    /**
     * @var string
     * @ApiFilter(OrderFilter::class, properties={OrderFilter::DESCENDING})
     */
    private $notes;
}