<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\NumericFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NumericFilterTest extends TestCase
{
    /** @var ApiFilter */
    private $apiFilter;

    /** @var Filter */
    private $filter;

    private $targetTableAlias = 'tableName';
    private $parameterName = 'year';
    private $filterType = 'constraint';
    /** @var MockObject|QueryNameGenerator */
    private $queryNameGenerator;

    protected function setUp(): void
    {
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->apiFilter = new ApiFilter([
            'value' => NumericFilter::class,
            'id' => $this->parameterName
        ]);

        $this->filter = new NumericFilter();
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

            $this->assertSame($expected, $result->getResult());

            $this->assertSame([$queryParameterName => $value], $result->getQueryParameters());

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