<?php

namespace MonterHealth\ApiFilterBundle;

use Doctrine\Common\Persistence\ManagerRegistry;
use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Annotation\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\Order;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use Doctrine\ORM\QueryBuilder;
use MonterHealth\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterHealthApiFilter
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
     * ManagerRegistry
     */
    private $managerRegistry;

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
    private $apiFilters = [];

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var string
     */
    private $targetTableAlias;

    public function __construct(iterable $filters, ApiFilterFactory $apiFilterFactory, ParameterCollectionFactory $parameterCollectionFactory, ManagerRegistry $managerRegistry)
    {
        $this->filters = $filters;
        $this->parameterCollectionFactory = $parameterCollectionFactory;
        $this->apiFilterFactory = $apiFilterFactory;
        $this->managerRegistry = $managerRegistry;
    }

    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
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
        if(null === $this->queryBuilder) {
            throw new \RuntimeException('Initialize service first with the initialize() method.');
        }

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