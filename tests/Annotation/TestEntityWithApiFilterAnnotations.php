<?php

namespace Monter\ApiFilterBundle\Tests\Annotation;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Filter\OrderFilter;
use Monter\ApiFilterBundle\Filter\BooleanFilter;
use Monter\ApiFilterBundle\Filter\SearchFilter;

/**
 * Class TestEntityWithApiFilterAnnotations
 * @package Monter\ApiFilterBundle\Tests
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