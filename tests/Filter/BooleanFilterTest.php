<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BooleanFilterTest extends TestCase
{
    private ApiFilter $apiFilter;
    private Filter $filter;
    private $targetTableAlias = 'tableName';
    private $filterType = 'constraint';
    private $parameterName;
    private MockObject | QueryNameGenerator $queryNameGenerator;

    protected function setUp(): void
    {
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->parameterName = 'active';

        $this->apiFilter = new ApiFilter(filterClass: BooleanFilter::class, id: $this->parameterName);

        $this->filter = new BooleanFilter();
    }

    public function testReturnsNullWhenParameterNotFound(): void
    {
        $parameterCollection = new Collection();

        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

        $this->assertNull($result);
    }

    public function testThrowsInvalidValueException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(sprintf('Invalid value used for query parameter \'%s\'.', $this->apiFilter->id));

        $this->filter->apply($this->targetTableAlias, $this->getFilledParameterCollection('invalid'), $this->apiFilter, $this->queryNameGenerator);
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
            $result = $this->filter->apply($this->targetTableAlias, $this->getFilledParameterCollection($correctValue), $this->apiFilter, $this->queryNameGenerator);

            $this->assertInstanceOf(FilterResult::class, $result);
            $this->assertSame($this->filterType, $result->getType());

            $target = $this->getTarget();
            $expected = sprintf('%s=%s', $target, $isTrue ? 'true' : 'false');

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
        $result = $this->filter->apply($this->targetTableAlias, $parameterCollection, $this->apiFilter, $this->queryNameGenerator);

        $this->assertInstanceOf(FilterResult::class, $result);
        $this->assertSame($this->filterType, $result->getType());

        $parameterCollection->rewind();
        $this->assertTrue($parameterCollection->current()->isUsed());

        $target = $parameterName;
        $expected = sprintf('%s=%s', $target, 'true');

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