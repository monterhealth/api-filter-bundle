<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\RangeFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RangeFilterTest extends TestCase
{
    private ApiFilter $apiFilter;
    private Filter $filter;
    private $targetTableAlias = 'tableName';
    private $parameterName = 'year';
    private $filterType = 'constraint';
    private MockObject | QueryNameGenerator $queryNameGenerator;

    protected function setUp(): void
    {
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->apiFilter = new ApiFilter(filterClass: RangeFilter::class, id: $this->parameterName);

        $this->filter = new RangeFilter();
    }

    public function testReturnsNullWhenParameterNotFound(): void
    {
        $parameterCollection = new Collection();

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

        $this->assertNull($result);
    }

    public function testEqualsStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'equals';
            $value = '2020';
            $queryParameterName = 'p1';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);
            $this->queryNameGenerator->expects($this->once())
                ->method('generateParameterName')
                ->with($this->getTarget())
                ->willReturn($queryParameterName);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? '!=' : '=';
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $this->assertSame([$queryParameterName => $value], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testMultipleStrategies(): void
    {
        $strategies = [

            [
                'name' => 'gte',
                'operator' => '>=',
            ],

            [
                'name' => 'lte',
                'operator' => '<=',
            ],

            [
                'name' => 'gt',
                'operator' => '>',
            ],

            [
                'name' => 'lt',
                'operator' => '<',
            ],

        ];

        $value = '2020';
        $queryParameterName = 'p1';

        foreach($strategies as $strategy) {

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy['name'], false);

            $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);
            $this->queryNameGenerator->expects($this->once())
                ->method('generateParameterName')
                ->with($this->getTarget())
                ->willReturn($queryParameterName);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $strategy['operator'];
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $this->assertSame([$queryParameterName => $value], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());

        }

    }

    public function testBetweenStrategy(): void
    {
        $strategy = 'bt';
        $value = '2000|2020';
        $queryParameterName = 'p1';

        $parameterCollection = $this->getFilledParameterCollection($value, $strategy, false);

        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);
        $this->queryNameGenerator->expects($this->once())
            ->method('generateParameterName')
            ->with($this->getTarget())
            ->willReturn($queryParameterName);

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

        $this->assertInstanceOf(FilterResult::class, $result);
        $this->assertSame($this->filterType, $result->getType());

        $parameterCollection->rewind();
        $this->assertTrue($parameterCollection->current()->isUsed());

        $expected = sprintf('%s >= :%s_0 AND %s <= :%s_1', $this->getTarget(), $queryParameterName, $this->getTarget(), $queryParameterName);
        $expected = '('.$expected.')';

        $this->assertSame($expected, $result->getResult());

        $queryParameters = [];
        foreach(explode('|', $value) as $num => $value) {

            $paramName = sprintf('%s_%s', $queryParameterName, $num);
            $queryParameters[$paramName] = $value;
        }

        $this->assertSame($queryParameters, $result->getQueryParameters());

        $this->assertSame([], $result->getJoins());
    }

    public function testGreaterThanStrategyInvalidValueException(): void
    {
        $strategy = 'gt';
        $value = 'abc';
        $queryParameterName = 'p1';

        $parameterCollection = $this->getFilledParameterCollection($value, $strategy, false);

        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);
        $this->queryNameGenerator->expects($this->once())
            ->method('generateParameterName')
            ->with($this->getTarget())
            ->willReturn($queryParameterName);

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid value used for query parameter \'year\'');

        $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);
    }

    public function testBetweenStrategyInvalidValueException(): void
    {
        $strategy = 'bt';
        $value = '2000|abc';
        $queryParameterName = 'p1';

        $parameterCollection = $this->getFilledParameterCollection($value, $strategy, false);

        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);
        $this->queryNameGenerator->expects($this->once())
            ->method('generateParameterName')
            ->with($this->getTarget())
            ->willReturn($queryParameterName);

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid value used for query parameter \'year\'');

        $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);
    }





    private function getFilledParameterCollection(string $value, string $strategy, bool $not): Collection
    {
        $command = new Command($value);
        $command->setOperator($strategy);
        if($not) {
            $command->setOperator('not');
        }

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