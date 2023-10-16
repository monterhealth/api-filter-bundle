<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/24/2018
 * Time: 4:54 PM
 */

namespace MonterHealth\ApiFilterBundle\Tests;


use Doctrine\ORM\QueryBuilder;
use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Attribute\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Factory\DefaultParameterCollectionFactory;
use MonterHealth\ApiFilterBundle\Tests\Attribute\TestEntityWithApiFilterAttributes;
use MonterHealth\ApiFilterBundle\Util\QueryNameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterHealthApiFilterTest extends TestCase
{
    // Real objects
    private MonterHealthApiFilter $monterHealthApiFilter;
    private ApiFilter $apiFilter;

    // Mocks
    private MockObject $apiFilterFactory;
    private MockObject $parameterCollectionFactory;
    private MockObject | QueryNameGenerator $queryNameGenerator;
    private MockObject | QueryBuilder $queryBuilder;
    private MockObject | ParameterBag $parameterBag;
    private MockObject $booleanFilter;
    private MockObject $parameterCollection;

    // Other
    private $className;
    private $configs = ['key' => 'value'];
    private $targetTableAlias = 'rootAlias';

    protected function setUp(): void
    {
        $this->className = TestEntityWithApiFilterAttributes::class;

        $this->apiFilterFactory = $this->createMock(ApiFilterFactory::class);
        $this->parameterCollectionFactory = $this->createMock(DefaultParameterCollectionFactory::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->parameterBag = $this->createMock(ParameterBag::class);
        $this->parameterCollection = $this->createMock(Collection::class);
        $this->booleanFilter = $this->createMock(BooleanFilter::class);
        $this->apiFilter = new ApiFilter(filterClass: get_class($this->booleanFilter));
        $this->queryNameGenerator = $this->createMock(QueryNameGenerator::class);

        $this->monterHealthApiFilter = new MonterHealthApiFilter([$this->booleanFilter], $this->apiFilterFactory, $this->parameterCollectionFactory, $this->queryNameGenerator);
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

        $this->monterHealthApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);
    }

    public function testGetFilterResultsThrowsException(): void
    {
        $className = get_class($this->monterHealthApiFilter);
        $propertyName = "targetTableAlias";

        $this->expectException(\Error::class);
        $this->expectExceptionMessage(sprintf('Typed property %s::$%s must not be accessed before initialization', $className, $propertyName));

        $this->monterHealthApiFilter->getFilterResults();
    }

    public function testGetFilterResults(): void
    {
        $this->queryBuilder->method('getRootAliases')->willReturn([$this->targetTableAlias]);
        $this->parameterCollectionFactory->method('create')->willReturn($this->parameterCollection);
        $this->apiFilterFactory->method('create')->willReturn([$this->apiFilter]);

        $this->monterHealthApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterHealthApiFilter->setConfigs($this->configs);

        $this->booleanFilter->expects($this->once())
            ->method('apply')
            ->with($this->targetTableAlias, $this->parameterCollection, $this->apiFilter, $this->queryNameGenerator, $this->configs);

        $this->monterHealthApiFilter->getFilterResults();
    }

    public function testApplyFilterResultsThrowsException(): void
    {
        $className = get_class($this->monterHealthApiFilter);
        $propertyName = "queryBuilder";

        $this->expectException(\Error::class);
        $this->expectExceptionMessage(sprintf('Typed property %s::$%s must not be accessed before initialization', $className, $propertyName));

        $this->monterHealthApiFilter->applyFilterResults([]);
    }

    public function testApplyFilterResultsConstraint(): void
    {
        $filterResult = $this->createMock(FilterResult::class);
        $filterResult->expects($this->once())->method('getType')->willReturn('constraint');
        $filterResult->expects($this->once())->method('getResult')->willReturn('fakeResult');

        $this->queryBuilder->expects($this->once())->method('andWhere')->with('fakeResult');

        $this->monterHealthApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterHealthApiFilter->applyFilterResults([$filterResult]);
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

        $this->monterHealthApiFilter->initialize($this->queryBuilder, $this->className, $this->parameterBag);

        $this->monterHealthApiFilter->applyFilterResults([$filterResult, $filterResult2]);
    }
}