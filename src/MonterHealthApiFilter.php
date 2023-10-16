<?php

namespace MonterHealth\ApiFilterBundle;

use Doctrine\ORM\Query\Expr\Join;
use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Attribute\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Filter\Filter;
use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use MonterHealth\ApiFilterBundle\Filter\Order;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use Doctrine\ORM\QueryBuilder;
use MonterHealth\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterHealthApiFilter
{

    private ParameterCollectionFactory $parameterCollectionFactory;
    private ApiFilterFactory $apiFilterFactory;
    private QueryNameGeneratorInterface $queryNameGenerator;
    private array $configs = [];
    private iterable $filters;
    private Collection $parameterCollection;
    private array $apiFilters = [];
    private QueryBuilder $queryBuilder;
    private string | null $targetTableAlias;

    public function __construct(iterable $filters, ApiFilterFactory $apiFilterFactory, ParameterCollectionFactory $parameterCollectionFactory, QueryNameGeneratorInterface $queryNameGenerator)
    {
        $this->filters = $filters;
        $this->parameterCollectionFactory = $parameterCollectionFactory;
        $this->apiFilterFactory = $apiFilterFactory;
        $this->queryNameGenerator = $queryNameGenerator;
    }

    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $className
     * @param ParameterBag $parameterBag
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function initialize(QueryBuilder $queryBuilder, string $className, ParameterBag $parameterBag): void
    {
        $this->queryBuilder = $queryBuilder;

        $this->targetTableAlias = $queryBuilder->getRootAliases() ? $queryBuilder->getRootAliases()[0] : null;

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
                    $result = $filter->apply($this->targetTableAlias, $this->parameterCollection, $apiFilter, $this->queryNameGenerator, $this->configs);
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
        // collect joins. process in group
        $joins = [];

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

                // bind parameters
                if($filterResult->hasQueryParameters()) {
                    foreach($filterResult->getQueryParameters() as $key => $value) {
                        $this->queryBuilder->setParameter($key, $value);
                    }
                }

                // collect joins
                foreach($filterResult->getJoins() as $targetTableAlias => $joinAlias) {
                    $joins[] = [
                        // targetTable: root or nested? nested = key of join
                        'targetTableAlias' => is_string($targetTableAlias) ? $targetTableAlias : $this->targetTableAlias,
                        'joinAlias' => $joinAlias,
                    ];
                }
            }
        }

        // process order filter results
        ksort($orderFilterResults);
        foreach($orderFilterResults as $filterResult) {
            $this->queryBuilder->addOrderBy($filterResult->getResult(), $filterResult->getSetting('ascending') ? 'ASC' : 'DESC');
        }

        // process joins
        foreach($joins as $join) {
            // check if join already exists in queryBuilder
            $match = $this->findMatchingJoin($join['joinAlias'], $this->queryBuilder->getDQLPart('join'));
            if(!$match) {
                // add join
                $this->queryBuilder->leftJoin(sprintf('%s.%s', $join['targetTableAlias'], $join['joinAlias']), $join['joinAlias']);
            }
        }
    }

    private function findMatchingJoin($joinAlias, array $joins): bool
    {
        foreach($joins as $rootAlias => $rootJoins) {
            if($rootAlias !== $this->targetTableAlias) { continue; }

            /** @var Join $join */
            foreach ($rootJoins as $join) {
                if($joinAlias === $join->getAlias()) {
                    return true;
                }
            }
        }

        return false;
    }
}