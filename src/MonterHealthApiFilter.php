<?php

namespace MonterHealth\ApiFilterBundle;

use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Query\Expr\Join;
use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Annotation\ApiFilterFactory;
use MonterHealth\ApiFilterBundle\Annotation\Reader;
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

    /**
     * @var ParameterCollectionFactory
     */
    private $parameterCollectionFactory;
    /**
     * @var ApiFilterFactory
     */
    private $apiFilterFactory;
    /**
     * @var QueryNameGeneratorInterface
     */
    private $queryNameGenerator;
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

	/**
	 * @var Reader
	 */
	private $reader;

	/**
	 * @var array
	 */
	private $embedded = [];

	/**
	 * @param iterable $filters
	 * @param ApiFilterFactory $apiFilterFactory
	 * @param ParameterCollectionFactory $parameterCollectionFactory
	 * @param QueryNameGeneratorInterface $queryNameGenerator
	 * @param Reader $reader
	 */
	public function __construct(iterable                    $filters,
	                            ApiFilterFactory            $apiFilterFactory,
	                            ParameterCollectionFactory  $parameterCollectionFactory,
	                            QueryNameGeneratorInterface $queryNameGenerator,
	                            Reader                      $reader
    )
    {
        $this->filters = $filters;
        $this->parameterCollectionFactory = $parameterCollectionFactory;
        $this->apiFilterFactory = $apiFilterFactory;
        $this->queryNameGenerator = $queryNameGenerator;
	    $this->reader = $reader;
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

		$this->embedded = $this->getEmbedded($className);
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
                    $result = $filter->apply($this->targetTableAlias,
	                    $this->parameterCollection,
	                    $apiFilter,
	                    $this->queryNameGenerator,
	                    $this->configs,
	                    $this->embedded
                    );
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

                // collect joins (merge)
                $joins = \array_merge($joins, $filterResult->getJoins());
            }
        }

        // process order filter results
        ksort($orderFilterResults);
        foreach($orderFilterResults as $filterResult) {
            $this->queryBuilder->addOrderBy($filterResult->getResult(), $filterResult->getSetting('ascending') ? 'ASC' : 'DESC');
        }

        // process joins
        foreach($joins as $targetTableAlias => $joinAlias) {
            // check if join already exists in queryBuilder
            $match = $this->findMatchingJoin($joinAlias, $this->queryBuilder->getDQLPart('join'));
            if(!$match) {
                // targetTable: root or nested? nested = key of join
                $targetTableAlias = is_string($targetTableAlias) ? $targetTableAlias : $this->targetTableAlias;
                // add join
                $this->queryBuilder->leftJoin(sprintf('%s.%s', $targetTableAlias, $joinAlias), $joinAlias);
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

	/**
	 * @throws ReflectionException
	 */
	private function getEmbedded(string $className) : array {

		$properties = [];

		$reflectionClass = new \ReflectionClass($className);

		foreach($reflectionClass->getProperties() as $property) {

			/** @var ApiFilter $filter */
			foreach($this->reader->getPropertyAnnotations($property) as $filter) {

				if(is_a($filter, Embedded::class, true)) {
					$properties[$property->name] = $property->name;
				}
			}
		}

		return $properties;
	}
}