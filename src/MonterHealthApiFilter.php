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
use MonterHealth\ApiFilterBundle\Parameter\Factory\GroupedParameterBagFactory;
use MonterHealth\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterHealthApiFilter
{
    /**
     * Orchestrates filter application against a Doctrine QueryBuilder.
     *
     * ## Lifecycle
     * Each HTTP request is one call to {@see addFilterConstraints()} (the primary entrypoint).
     * The method is stateful per call: it stores queryBuilder, targetTableAlias, apiFilters and
     * parameterCollection as instance state while processing, then writes constraints, joins and
     * ordering to the QueryBuilder.
     *
     * ## Flat vs grouped mode
     * - No mh_groups[…] keys in the query → flat mode: all filters combined with AND.
     * - mh_groups[…] keys present → grouped mode: each group is resolved independently and
     *   top-level constraints are combined as (group0) AND (group1) AND … Non-group params are
     *   treated as an implicit final AND group; order[…] params are applied globally.
     *
     * ## Extending
     * Custom filters can be added by implementing {@see Filter} and tagging the service with
     * `monter_health_api_filter`. The factories (ParameterCollectionFactory, ApiFilterFactory,
     * AttributeReader) are swappable via bundle config.
     */

    private ParameterCollectionFactory $parameterCollectionFactory;
    private ApiFilterFactory $apiFilterFactory;
    private QueryNameGeneratorInterface $queryNameGenerator;
    private array $configs = [];
    private iterable $filters;
    private ?Collection $parameterCollection = null;
    private array $apiFilters = [];
    private QueryBuilder $queryBuilder;
    private ?string $targetTableAlias = null;

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
     * Prefix for grouped filter query parameters (see bundle config `filter_groups_query_prefix`).
     *
     * Public so controllers/integrators can keep request parsing aligned with bundle config.
     */
    public function getFilterGroupsQueryPrefix(): string
    {
        return $this->configs['filter_groups_query_prefix'] ?? GroupedParameterBagFactory::DEFAULT_PREFIX;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $className
     * @param ParameterBag $parameterBag
     * @throws ReflectionException
     */
    public function addFilterConstraints(QueryBuilder $queryBuilder, string $className, ParameterBag $parameterBag): void
    {
        // Auto-detect grouped query mode: once any mh_groups[...] key is present, grouped semantics apply.
        $split = GroupedParameterBagFactory::splitQueryBag($parameterBag, $this->getFilterGroupsQueryPrefix());
        if ([] !== $split['groups']) {
            $groups = $split['groups'];
            // Non-group query params become an implicit final AND group.
            if ($split['globals']->count() > 0) {
                $groups[] = $split['globals'];
            }
            // Keep globals separately for ordering (order filter results are read from this bag).
            $this->addFilterConstraintsGrouped($queryBuilder, $className, $groups, $split['globals']);

            return;
        }

        // Backward-compatible path: no groups detected, keep legacy flat behavior.
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
        $this->prepareExecutionContext($queryBuilder, $className);
        $this->parameterCollection = $this->parameterCollectionFactory->create($parameterBag);
    }

    /**
     * @return array<int, FilterResult>
     */
    public function getFilterResults(): array
    {
        if(null === $this->targetTableAlias || null === $this->apiFilters || null === $this->parameterCollection) {
            throw new \RuntimeException('Initialize service first with the initialize() method.');
        }

        return $this->collectFilterResults($this->parameterCollection);
    }

    /**
     * Applies grouped constraints with top-level AND semantics: (group0) AND (group1) AND …
     *
     * Only constraint results are taken from {@see $groups}. If you also want non-group query filters as
     * constraints, append them as an extra group before calling this method.
     *
     * {@see $orderAndGlobals} is used for ordering only (order filter results).
     *
     * @param list<ParameterBag> $groups
     *
     * @throws ReflectionException
     */
    public function addFilterConstraintsGrouped(QueryBuilder $queryBuilder, string $className, array $groups, ?ParameterBag $orderAndGlobals = null): void
    {
        $this->prepareExecutionContext($queryBuilder, $className);
        $this->parameterCollection = null;

        $resultsToApply = [];

        foreach ($groups as $groupBag) {
            if (!$groupBag instanceof ParameterBag) {
                throw new \InvalidArgumentException('Each group must be an instance of ' . ParameterBag::class);
            }
            if (0 === $groupBag->count()) {
                continue;
            }
            $collection = $this->parameterCollectionFactory->create($groupBag);
            $groupResults = $this->collectFilterResults($collection);
            $constraints = [];
            foreach ($groupResults as $result) {
                if ('constraint' === $result->getType()) {
                    $constraints[] = $result;
                }
            }
            $merged = $this->mergeConstraintFilterResults($constraints);
            if ($merged instanceof FilterResult) {
                $resultsToApply[] = $merged;
            }
        }

        if (null !== $orderAndGlobals && $orderAndGlobals->count() > 0) {
            $globalCollection = $this->parameterCollectionFactory->create($orderAndGlobals);
            foreach ($this->collectFilterResults($globalCollection) as $result) {
                if ('order' === $result->getType()) {
                    $resultsToApply[] = $result;
                }
            }
        }

        $this->applyFilterResults($resultsToApply);
    }

    /**
     * Collect filter results for a specific parameter collection.
     *
     * This is reused by both flat and grouped code paths so filter execution stays identical
     * regardless of how query params are supplied.
     *
     * @return array<int, FilterResult>
     */
    public function collectFilterResults(Collection $parameterCollection): array
    {
        if (null === $this->targetTableAlias) {
            throw new \RuntimeException('Set targetTableAlias before collecting filter results.');
        }

        $results = [];

        foreach ($this->filters as $filter) {
            foreach ($this->apiFilters as $apiFilter) {
                if (is_a($apiFilter->filterClass, \get_class($filter), true)) {
                    $result = $filter->apply($this->targetTableAlias, $parameterCollection, $apiFilter, $this->queryNameGenerator, $this->configs);
                    if ($result instanceof FilterResult) {
                        $results[] = $result;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Merge all constraint results from one logical group into a single AND-wrapped constraint.
     *
     * Returning one FilterResult per group preserves explicit top-level group boundaries when
     * constraints are later applied to the QueryBuilder.
     *
     * @param list<FilterResult> $constraintResults
     */
    private function mergeConstraintFilterResults(array $constraintResults): ?FilterResult
    {
        // Defensive filter: keep only real constraint results.
        $constraintResults = array_values(array_filter(
            $constraintResults,
            static fn ($r) => $r instanceof FilterResult && 'constraint' === $r->getType()
        ));
        if ([] === $constraintResults) {
            return null;
        }
        // Single constraint: preserve as-is to avoid unnecessary wrapping.
        if (1 === \count($constraintResults)) {
            return $constraintResults[0];
        }

        $parts = [];
        $queryParams = [];
        $mergedJoins = [];
        foreach ($constraintResults as $fr) {
            $parts[] = $fr->getResult();
            // Parameter names are already generated uniquely; this keeps later entries deterministic.
            $queryParams = array_replace($queryParams, $fr->getQueryParameters());
            // Joins are merged for the grouped result; QueryBuilder-level dedupe still applies later.
            $mergedJoins = array_replace($mergedJoins, $fr->getJoins());
        }

        // Explicit group wrapper preserves (f1 AND f2 AND ...) semantics as one top-level constraint.
        $dql = '('.implode(') AND (', $parts).')';

        return new FilterResult('constraint', $dql, $queryParams, $mergedJoins, []);
    }

    /**
     * @param array $filterResults
     */
    public function applyFilterResults(array $filterResults): void
    {
        if(null === $this->queryBuilder) {
            throw new \RuntimeException('Initialize service first with the initialize() method.');
        }

        // collect filter results with the type 'order' first. they need to be ordered in the right sequence
        $orderFilterResults = [];
        // collect joins. process in group
        $joins = [];

        foreach($filterResults as $filterResult) {
            if($filterResult instanceof FilterResult) {
                // 'constraint' mutates WHERE immediately; 'order' is deferred and sorted by sequence.
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

    /**
     * Store shared execution context used by both flat and grouped code paths.
     *
     * Keeps query builder, root alias, and entity metadata in a consistent state before
     * any filter results are collected. Both {@see initialize()} and {@see addFilterConstraintsGrouped()}
     * start from this same baseline.
     */
    private function prepareExecutionContext(QueryBuilder $queryBuilder, string $className): void
    {
        $this->queryBuilder = $queryBuilder;
        $this->targetTableAlias = $queryBuilder->getRootAliases() ? $queryBuilder->getRootAliases()[0] : null;
        $this->apiFilters = $this->apiFilterFactory->create($className);
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