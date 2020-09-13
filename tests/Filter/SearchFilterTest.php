<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchFilterTest extends TestCase
{
    /** @var ApiFilter */
    private $apiFilter;

    /** @var Filter */
    private $filter;

    private $targetTableAlias = 'tableName';
    private $parameterName = 'name';
    private $filterType = 'constraint';
    /** @var MockObject|QueryNameGenerator */
    private $queryNameGenerator;

    protected function setUp(): void
    {
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->apiFilter = new ApiFilter([
            'value' => SearchFilter::class,
            'id' => $this->parameterName
        ]);

        $this->filter = new SearchFilter();
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
            $value = 'jimi hendrix';
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

    public function testPartialStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'partial';
            $value = 'adiohea';
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

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $resultValue = sprintf('%%%s%%', $value);
            $this->assertSame([$queryParameterName => $resultValue], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testStartStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'start';
            $value = 'bloodhoundga';
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

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $resultValue = sprintf('%s%%', $value);
            $this->assertSame([$queryParameterName => $resultValue], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testEndStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'end';
            $value = 'nk floyd';
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

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $resultValue = sprintf('%%%s', $value);
            $this->assertSame([$queryParameterName => $resultValue], $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testWordStartStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'word_start';
            $value = 'cold';
            $queryParameterName = 'tableName_name_p1';
            $queryParameterName2 = 'tableName_name_p2';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $this->queryNameGenerator = new QueryNameGenerator();

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName);
            $expected .= $isNot ? ' AND ' : ' OR ';
            $expected .= sprintf('%s %s :%s', $this->getTarget(), $operator, $queryParameterName2);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $resultValue1 = sprintf('%s%%', $value);
            $resultValue2 = sprintf('%% %s%%', $value);

            $queryParameterResult = [
                $queryParameterName => $resultValue1,
                $queryParameterName2 => $resultValue2
            ];

            $this->assertSame($queryParameterResult, $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
    }

    public function testInStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'in';
            $values = [
                'p1_0' => 'Radiohead',
                'p1_1' => 'Death Cab for Cutie',
            ];
            $value = implode(Command::VALUE_SEPARATOR, $values);
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

            $operator = $isNot ? 'NOT' : '';
            $expected = sprintf('%s %s IN(%s)', $this->getTarget(), $operator, ':'.implode(', :', array_keys($values)));
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());

            $this->assertSame($values, $result->getQueryParameters());

            $this->assertSame([], $result->getJoins());
        }
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