<?php

namespace Monter\ApiFilterBundle\Tests\Annotation;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Filter\BooleanFilter;
use Monter\ApiFilterBundle\Filter\Filter;
use Monter\ApiFilterBundle\Filter\SearchFilter;
use PHPUnit\Framework\TestCase;

class ApiFilterTest extends TestCase
{
    public function test__construct(): void
    {
        // value must be set

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This annotation needs a value representing the filter class.');

        new ApiFilter([]);

        // value must be a string

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This annotation needs a value representing the filter class.');

        new ApiFilter(['value' => []]);

        // value must be a className that implements the right filter interface

        $value = 'Testing';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('The filter class "%s" does not implement "%s".', $value, Filter::class));

        new ApiFilter(['value' => $value]);

        // filterClass gets set

        $value = BooleanFilter::class;
        $apiFilter = new ApiFilter(['value' => $value]);

        $this->assertSame($value, $apiFilter->filterClass);

        // throws error when property does not exist on class

        $invalidProperty = 'invalidProperty';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Property "%s" does not exist on the ApiFilter annotation.', $invalidProperty));

        new ApiFilter(['value' => BooleanFilter::class, $invalidProperty => 'invalid']);

        // sets options correctly on properties of class

        $options = [
            'value' => SearchFilter::class,
            'properties' => ['parameter_1' => 'word_start'],
            'arguments' => ['concatenate' => ['column_1', 'column_2']]
        ];

        $apiFilter = new ApiFilter($options);

        $this->assertSame($options['properties'], $apiFilter->properties);
        $this->assertSame($options['arguments'], $apiFilter->arguments);
    }
}