<?php

namespace Monter\ApiFilterBundle;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Annotation\ApiFilterFactory;
use Monter\ApiFilterBundle\Filter\Filter;
use Monter\ApiFilterBundle\Filter\FilterResult;
use Monter\ApiFilterBundle\Filter\Order;
use Monter\ApiFilterBundle\Parameter\Collection;
use Doctrine\ORM\QueryBuilder;
use Monter\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterApiFilter
{

    /**
     * @var ParameterCollectionFactory
     */
    private $parameterCollectionFactory;
    /**
     * @var ApiFilterFactory
     */
    private $apiFilterFactory;

    /**
     * Bundle configs
     * @var array
     */
    private $configs = [];

    /**
     * @var Filter[]|Order[]
     */
    private $filters;

    /**
     * @var Collection
     */
    private $parameterCollection;

    /**
     * @var ApiFilter[]
     */
    private $apiFilters;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var string
     */
    private $targetTableAlias;

    public function __construct(ApiFilterFactory $apiFilterFactory, ParameterCollectionFactory $parameterCollectionFactory)
    {
        $this->parameterCollectionFactory = $parameterCollectionFactory;
        $this->apiFilterFactory = $apiFilterFactory;
    }

    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    public function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $className
     * @param ParameterBag $parameterBag
     * @throws \ReflectionException
     */
    public function addFilterConstraints(QueryBuilder $queryBuilder, string $className, ParameterBag $parameterBag): void
    {
        $this->initialize($queryBuilder, $className, $parameterBag);

        $this->applyFilterResults($this->getFilterResults());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $className
     * @param ParameterBag $parameterBag
     * @throws \ReflectionException
     */
    public function initialize(QueryBuilder $queryBuilder, string $className, ParameterBag $parameterBag): void
    {
        $this->queryBuilder = $queryBuilder;

        $this->targetTableAlias = $queryBuilder->getRootAliases()[0];

        $this->parameterCollection = $this->parameterCollectionFactory->create($parameterBag);

        $this->apiFilters = $this->apiFilterFactory->create($className);
    }

    /**
     * @return array
     */
    public function getFilterResults(): array
    {
        if(null === $this->targetTableAlias || null === $this->apiFilters || null === $this->parameterCollection) {
            throw new \RuntimeException('Initialize service first with the initialize() method.');
        }

        $results = [];

        foreach($this->filters as $filter) {
            foreach($this->apiFilters as $apiFilter) {
                if(is_a($apiFilter->filterClass, \get_class($filter), true)) {
                    $result = $filter->apply($this->targetTableAlias, $this->parameterCollection, $apiFilter, $this->configs);
                    if($result instanceof FilterResult) {
                        $results[] = $result;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param array $filterResults
     */
    public function applyFilterResults(array $filterResults): void
    {
        // collect filter results with type 'order' first. they need to be ordered in the right sequence
        $orderFilterResults = [];

        foreach($filterResults as $filterResult) {
            if($filterResult instanceof FilterResult) {
                switch($filterResult->getType()) {

                    case 'constraint':
                        $this->queryBuilder->andWhere($filterResult->getResult());
                        break;

                    case 'order':
                        $orderFilterResults[$filterResult->getSetting('sequence')] = $filterResult;
                        break;
                }
            }
        }

        ksort($orderFilterResults);

        foreach($orderFilterResults as $filterResult) {
            $this->queryBuilder->addOrderBy($filterResult->getResult(), $filterResult->getSetting('ascending') ? 'ASC' : 'DESC');
        }
    }
}