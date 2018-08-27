<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/22/2018
 * Time: 8:01 PM
 */

namespace MonterHealth\ApiFilterBundle\Tests\Annotation;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Annotation\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Annotation\Reader;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use PHPUnit\Framework\TestCase;

class ApiFilterFactoryTest extends TestCase
{
    /** @var ApiFilterFactory */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ApiFilterFactory(new Reader());
    }

    public function testCreate(): void
    {
        $result = $this->factory->create(TestEntityWithApiFilterAnnotations::class);

        $this->assertCount(6, $result);

        foreach($result as $apiFilter) {
            self::assertInstanceOf(ApiFilter::class, $apiFilter);
        }

        /** @var ApiFilter $apiFilter */
        // active: boolean filter
        $apiFilter = $result[0];
        $this->assertSame('active', $apiFilter->id);
        $this->assertNull($apiFilter->strategy);
        $this->assertSame(BooleanFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);

        // id: order filter
        $apiFilter = $result[1];
        $this->assertSame('id', $apiFilter->id);
        $this->assertNull($apiFilter->strategy);
        $this->assertSame(OrderFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);

        // name: order filter
        $apiFilter = $result[2];
        $this->assertSame('name', $apiFilter->id);
        $this->assertSame('ASCENDING', $apiFilter->strategy);
        $this->assertSame(OrderFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);

        // name: order filter
        $apiFilter = $result[3];
        $this->assertSame('active', $apiFilter->id);
        $this->assertSame('DESCENDING', $apiFilter->strategy);
        $this->assertSame(OrderFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);

        // name: boolean filter
        $apiFilter = $result[4];
        $this->assertSame('name', $apiFilter->id);
        $this->assertNull($apiFilter->strategy);
        $this->assertSame(SearchFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);

        // notes: order filter
        $apiFilter = $result[5];
        $this->assertSame('notes', $apiFilter->id);
        $this->assertSame('DESCENDING', $apiFilter->strategy);
        $this->assertSame(OrderFilter::class, $apiFilter->filterClass);
        $this->assertSame([], $apiFilter->properties);
        $this->assertSame([], $apiFilter->arguments);
    }
}