<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/24/2018
 * Time: 4:54 PM
 */

namespace Monter\ApiFilterBundle\Tests;


use Doctrine\ORM\QueryBuilder;
use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Annotation\ApiFilterFactory;
use Monter\ApiFilterBundle\Filter\BooleanFilter;
use Monter\ApiFilterBundle\Filter\FilterResult;
use Monter\ApiFilterBundle\MonterApiFilter;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Factory\DefaultParameterCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterApiFilterTest extends TestCase
{
    // Real objects
    /** @var MonterApiFilter */
    private $monterApiFilter;
    /** @var ApiFilter */
    private $apiFilter;

    // Mocks
    /** @var MockObject */
    private $apiFilterFactory;
    /** @var MockObject */
    private $parameterCollectionFactory;
    /** @var MockObject */
    private $queryBuilder;
    /** @var MockObject */
    private $parameterBag;
    /** @var MockObject */
    private $booleanFilter;
    /** @var MockObject */
    private $parameterCollection;

    // Other
    private $className;
    private $configs = ['key' => 'value'];
    private $targetTableAlias = 'rootAlias';

    protected function setUp()
    {
        $this->className = TestEntityWithApiFilterAnnotations::class;

        $this->apiFilterFactory = $this->createMock(ApiFilterFactory::class);
        $this->parameterCollectionFactory = $this->createMock(DefaultParameterCollectionFactory::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->parameterBag = $this->createMock(ParameterBag::class);
        $this->parameterCollection = $this->createMock(Collection::class);
        $this->booleanFilter = $this->createMock(BooleanFilter::class);
        $this->apiFilter = new ApiFilter(['value' => \get_class($this->booleanFilter)]);

        $this->monterApiFilter = new MonterApiFilter([$this->booleanFilter], $this->apiFilterFactory, $this->parameterCollectionFactory);
    }

    public function testInitialize(): void
    {
        $this->queryBuilder->expects($this->once())->method('getRootAliases');

        $this->parameterCollectionFactory->expects($this->once())
            ->method('create')
            ->with($this->parameterBag)
        ;

        $this->apiFilterFactory->expects($this->once())
            ->method('create')
            ->with($this->className)
        ;

        $this->monterApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);
    }

    public function testGetFilterResultsThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Initialize service first with the initialize() method.');

        $this->monterApiFilter->getFilterResults();
    }

    public function testGetFilterResults(): void
    {
        $this->queryBuilder->method('getRootAliases')->willReturn([$this->targetTableAlias]);
        $this->parameterCollectionFactory->method('create')->willReturn($this->parameterCollection);
        $this->apiFilterFactory->method('create')->willReturn([$this->apiFilter]);

        $this->monterApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterApiFilter->setConfigs($this->configs);

        $this->booleanFilter->expects($this->once())
            ->method('apply')
            ->with($this->targetTableAlias, $this->parameterCollection, $this->apiFilter, $this->configs);

        $this->monterApiFilter->getFilterResults();
    }

    public function testApplyFilterResultsThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Initialize service first with the initialize() method.');

        $this->monterApiFilter->applyFilterResults([]);
    }

    public function testApplyFilterResultsConstraint(): void
    {
        $filterResult = $this->createMock(FilterResult::class);
        $filterResult->expects($this->once())->method('getType')->willReturn('constraint');
        $filterResult->expects($this->once())->method('getResult')->willReturn('fakeResult');

        $this->queryBuilder->expects($this->once())->method('andWhere')->with('fakeResult');

        $this->monterApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterApiFilter->applyFilterResults([$filterResult]);
    }

    public function testApplyFilterResultsOrder(): void
    {
        $filterResult = $this->createMock(FilterResult::class);
        $filterResult->expects($this->once())->method('getType')->willReturn('order');
        $filterResult->expects($this->once())->method('getResult')->willReturn('fakeResult');

        $filterResult->expects($this->exactly(2))
            ->method('getSetting')
            ->will($this->returnValueMap([['sequence', 2], ['ascending', false]]));

        $filterResult2 = $this->createMock(FilterResult::class);
        $filterResult2->expects($this->once())->method('getType')->willReturn('order');
        $filterResult2->expects($this->once())->method('getResult')->willReturn('fakeResult2');

        $filterResult2->expects($this->exactly(2))
            ->method('getSetting')
            ->will($this->returnValueMap([['sequence', 1], ['ascending', true]]));

        $this->queryBuilder->expects($this->exactly(2))->method('addOrderBy')
            ->withConsecutive(['fakeResult2', 'ASC'], ['fakeResult', 'DESC']);

        $this->monterApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterApiFilter->applyFilterResults([$filterResult, $filterResult2]);
    }
}