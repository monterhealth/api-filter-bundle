<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/23/2018
 * Time: 9:15 PM
 */

namespace Monter\ApiFilterBundle\Tests\Filter;


use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Filter\Filter;
use Monter\ApiFilterBundle\Filter\FilterResult;
use Monter\ApiFilterBundle\Filter\OrderFilter;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;
use Monter\ApiFilterBundle\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class OrderFilterTest extends TestCase
{
    /** @var ApiFilter */
    private $apiFilter;

    /** @var Filter */
    private $filter;

    private $targetTableAlias = 'tableName';
    private $filterType = 'order';
    private $parameterName;

    protected function setUp()
    {
        $this->parameterName = 'order';

        $this->apiFilter = new ApiFilter([
            'value' => OrderFilter::class,
            'id' => $this->parameterName
        ]);

        $this->filter = new OrderFilter();
    }

    public function testReturnsNullWhenParameterNotFound(): void
    {
        $parameterCollection = new Collection();

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

        $this->assertNull($result);
    }

    public function testCorrectResult(): void
    {
        $value = 'id';
        $this->apiFilter->id = $value;

        $strategies = [
            'asc' => true,
            'desc' => false,
        ];

        foreach($strategies as $strategy => $isAscending) {
            $parameterCollection = $this->getFilledParameterCollection($value, $strategy);
            $configs = ['order_parameter_name' => $this->parameterName];

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $configs);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertFalse($parameterCollection->current()->isUsed());

            $target = $this->targetTableAlias;
            $expected = sprintf('%s.%s', $target, $value);

            $this->assertSame($expected, $result->getResult());
            $this->assertSame($isAscending, $result->getSetting('ascending'));
        }
    }

    private function getFilledParameterCollection(string $value, string $strategy): Collection
    {
        $command = new Command($value);
        $command->setOperator($strategy);

        $parameter = new Parameter($this->parameterName);
        $parameter->addCommand($command);

        $parameterCollection = new Collection();
        $parameterCollection->add($parameter);

        return $parameterCollection;
    }

    private function getTarget(): string
    {
        return $this->targetTableAlias.'.'.$this->apiFilter->id;
    }
}