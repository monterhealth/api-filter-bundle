<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/23/2018
 * Time: 9:15 PM
 */

namespace Monter\ApiFilterBundle\Tests\Filter;


use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Filter\BooleanFilter;
use Monter\ApiFilterBundle\Filter\Filter;
use Monter\ApiFilterBundle\Filter\FilterResult;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;
use Monter\ApiFilterBundle\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class BooleanFilterTest extends TestCase
{
    /** @var ApiFilter */
    private $apiFilter;

    /** @var Filter */
    private $filter;

    private $targetTableAlias = 'tableName';
    private $filterType = 'constraint';
    private $parameterName;

    protected function setUp()
    {
        $this->parameterName = 'active';

        $this->apiFilter = new ApiFilter([
            'value' => BooleanFilter::class,
            'id' => $this->parameterName
        ]);

        $this->filter = new BooleanFilter();
    }

    public function testReturnsNullWhenParameterNotFound(): void
    {
        $parameterCollection = new Collection();

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

        $this->assertNull($result);
    }

    public function testThrowsInvalidValueException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid value used for query parameter \'%s\'.', $this->apiFilter->id));

        $this->filter->apply($this->targetTableAlias, $this->getFilledParameterCollection('invalid'), $this->apiFilter);
    }

    public function testCorrectResult(): void
    {
        $correctValues = [
            'true' => true,
            '1' => true,
            1 => true,
            'false' => false,
            '0' => false,
            0 => false,
        ];

        foreach($correctValues as $correctValue => $isTrue) {
            $result = $this->filter->apply($this->targetTableAlias, $this->getFilledParameterCollection($correctValue), $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $target = $this->getTarget();
            $expected = sprintf('%s=%b', $target, $isTrue ? 1 : 0);

            $this->assertSame($expected, $result->getResult());
        }
    }

    public function testNestedParameter(): void
    {
        $value = 'true';

        $parameterName = 'nested.active';
        $this->parameterName = $parameterName;
        $this->apiFilter->id = $parameterName;

        $parameterCollection = $this->getFilledParameterCollection($value);
        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

        $this->assertInstanceOf(FilterResult::class, $result);
        $this->assertSame($this->filterType, $result->getType());

        $parameterCollection->rewind();
        $this->assertTrue($parameterCollection->current()->isUsed());

        $target = $parameterName;
        $expected = sprintf('%s=%b', $target, 1);

        $this->assertSame($expected, $result->getResult());
    }

    private function getFilledParameterCollection(string $value): Collection
    {
        $command = new Command($value);

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