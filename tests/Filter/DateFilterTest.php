<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\DateFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateFilterTest extends TestCase
{
    private ApiFilter $apiFilter;
    private Filter $filter;
    private $targetTableAlias = 'tableName';
    private $parameterName = 'date';
    private $filterType = 'constraint';
    private MockObject | QueryNameGenerator $queryNameGenerator;

    protected function setUp(): void
    {
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->apiFilter = new ApiFilter(filterClass: DateFilter::class, id: $this->parameterName);

        $this->filter = new DateFilter();
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
            $value = '2020-12-06';
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

            // get result value: must be a DateTime
            $resultParam = $result->getQueryParameters()[$queryParameterName];
            $this->assertIsObject($resultParam);
            $this->assertTrue($resultParam instanceof \DateTimeInterface, 'Result value must be a DateTime.');
            $this->assertEquals($value, $resultParam->format('Y-m-d'), 'expect the right date value.');

            $this->assertSame([$queryParameterName => $resultParam], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testMultipleStrategies(): void
    {
        $strategies = [

            [
                'name' => 'after',
                'operator' => '>=',
                'null' => false,
            ],

            [
                'name' => 'before',
                'operator' => '<=',
                'null' => false,
            ],

            [
                'name' => 'strictly_after',
                'operator' => '>',
                'null' => false,
            ],

            [
                'name' => 'strictly_before',
                'operator' => '<',
                'null' => false,
            ],

            [
                'name' => 'null_or_after',
                'operator' => '>=',
                'null' => true,
            ],

            [
                'name' => 'null_or_before',
                'operator' => '<=',
                'null' => true,
            ],

            [
                'name' => 'null_or_strictly_after',
                'operator' => '>',
                'null' => true,
            ],

            [
                'name' => 'null_or_strictly_before',
                'operator' => '<',
                'null' => true,
            ],

        ];

        $value = '2020-12-06';
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
            if($strategy['null']) {
                $expected = sprintf('%s IS NULL OR %s', $this->getTarget(), $expected);
            }
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            // get result value: must be a DateTime
            $resultParam = $result->getQueryParameters()[$queryParameterName];
            $this->assertIsObject($resultParam);
            $this->assertTrue($resultParam instanceof \DateTimeInterface, 'Result value must be a DateTime.');
            $this->assertEquals($value, $resultParam->format('Y-m-d'), 'expect the right date value.');

            $this->assertSame([$queryParameterName => $resultParam], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());

        }

    }


    public function testInvalidValueException(): void
    {
        $strategy = 'equals';
        $value = 'abc';
        $queryParameterName = 'p1';

        $parameterCollection = $this->getFilledParameterCollection($value, $strategy, false);

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid value used for query parameter \'date\'');

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