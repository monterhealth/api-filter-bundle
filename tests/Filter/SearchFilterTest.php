<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/23/2018
 * Time: 9:15 PM
 */

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
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

    protected function setUp()
    {
        $this->apiFilter = new ApiFilter([
            'value' => SearchFilter::class,
            'id' => $this->parameterName
        ]);

        $this->filter = new SearchFilter();
    }

    public function testReturnsNullWhenParameterNotFound(): void
    {
        $parameterCollection = new Collection();

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

        $this->assertNull($result);
    }

    public function testEqualsStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'equals';
            $value = 'jimi hendrix';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? '!=' : '=';
            $expected = sprintf('%s%s\'%s\'', $this->getTarget(), $operator, $value);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());
        }
    }

    public function testPartialStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'partial';
            $value = 'adiohea';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s \'%%%s%%\'', $this->getTarget(), $operator, $value);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());
        }
    }

    public function testStartStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'start';
            $value = 'bloodhoundga';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s \'%s%%\'', $this->getTarget(), $operator, $value);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());
        }
    }

    public function testEndStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'end';
            $value = 'nk floyd';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s \'%%%s\'', $this->getTarget(), $operator, $value);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());
        }
    }

    public function testWordStartStrategy(): void
    {
        $isNots = [true, false];

        foreach($isNots as $isNot) {
            $strategy = 'word_start';
            $value = 'cold';

            $parameterCollection = $this->getFilledParameterCollection($value, $strategy, $isNot);

            $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $parameterCollection->rewind();
            $this->assertTrue($parameterCollection->current()->isUsed());

            $operator = $isNot ? 'NOT LIKE' : 'LIKE';
            $expected = sprintf('%s %s \'%s%%\'', $this->getTarget(), $operator, $value);
            $expected .= $isNot ? ' AND ' : ' OR ';
            $expected .= sprintf('%s %s \'%% %s%%\'', $this->getTarget(), $operator, $value);
            $expected = '('.$expected.')';

            $this->assertSame($expected, $result->getResult());
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