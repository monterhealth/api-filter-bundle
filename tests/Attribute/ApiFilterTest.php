<?php

namespace MonterHealth\ApiFilterBundle\Tests\Attribute;

use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use PHPUnit\Framework\TestCase;

class ApiFilterTest extends TestCase
{
    public function test__construct1(): void
    {
        // value must be set

        $this->expectException(\ArgumentCountError::class);
        new ApiFilter();
    }

    public function test__construct2(): void
    {
        // value must be a string

        $this->expectException(\TypeError::class);
        new ApiFilter([]);
    }

    public function test__construct3(): void
    {
        // value must be a className that implements the right filter interface

        $value = 'Testing';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The filter class "%s" does not implement "%s".', $value, Filter::class));

        new ApiFilter($value);
    }

    public function test__construct4(): void
    {
        // filterClass gets set

        $value = BooleanFilter::class;
        $apiFilter = new ApiFilter($value);

        $this->assertSame($value, $apiFilter->filterClass);
    }

    public function test__construct5(): void
    {
        // sets options correctly on properties of class

        $value = SearchFilter::class;
        $properties = ['parameter_1' => 'word_start'];
        $arguments = ['concatenate' => ['column_1', 'column_2']];

        $apiFilter = new ApiFilter($value, $properties, $arguments);

        $this->assertSame($properties, $apiFilter->properties);
        $this->assertSame($arguments, $apiFilter->arguments);
    }
}