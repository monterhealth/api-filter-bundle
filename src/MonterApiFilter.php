<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/10/2018
 * Time: 1:25 PM
 */

namespace Monter\ApiFilterBundle;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Filter\Filter;
use Monter\ApiFilterBundle\Filter\Order;
use Monter\ApiFilterBundle\Parameter\Collection;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Monter\ApiFilterBundle\Parameter\Factory\ParameterCollectionFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

class MonterApiFilter
{

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ParameterCollectionFactory
     */
    private $parameterCollectionFactory;

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

    /** @var ClassMetadata */
    private $targetEntity;


    public function __construct(Reader $reader, ParameterCollectionFactory $parameterCollectionFactory)
    {
        $this->reader = $reader;
        $this->parameterCollectionFactory = $parameterCollectionFactory;
    }

    public function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $className
     * @param ParameterBag $parameterBag
     */
    public function addFilterConstraints(QueryBuilder $queryBuilder, string $className, ParameterBag $parameterBag): void
    {
        $this->targetEntity = $queryBuilder->getEntityManager()->getClassMetadata($className);

        $targetTableAlias = $queryBuilder->getRootAliases()[0];

        $this->parameterCollection = $this->createParameterCollectionFromParameterBag($parameterBag);

        $this->apiFilters = $this->getAnnotations();

        $constraints = [];
        $orders = [];

        foreach($this->filters as $filter) {
            foreach($this->apiFilters as $apiFilter) {
                if(is_a($apiFilter->filterClass, \get_class($filter), true)) {

                    $response = $filter->apply($targetTableAlias, $this->parameterCollection, $apiFilter, $this->configs);
                    if(\strlen($response)) {

                        if(is_a($filter, Order::class)) {
                            $orders[$filter->getSequence()] = [
                                'sort' => $response,
                                'strategy' => $filter->isAscending() ? 'ASC' : 'DESC'
                            ];
                        } else {
                            $constraints[] = $response;
                        }
                    }
                }
            }
        }

        $constraint = trim(implode(') AND (', $constraints));
        $constraint = \strlen($constraint) > 0 ? '('. $constraint. ')' : $constraint;

        if(strlen($constraint)) {
            $queryBuilder->andWhere($constraint);
        }

        foreach($orders as $order) {
            $queryBuilder->addOrderBy($order['sort'], $order['strategy']);
        }
// TODO: split into separate, callable methods + add methods that tell the number of constraints and orders
    }

    /**
     * Get the ApiFilter settings from the Root Entity, set by the @ApiFilter annotations
     * @return array
     */
    private function getAnnotations(): array
    {
        return array_merge($this->getClassAnnotations(), $this->getPropertyAnnotations());
    }

    /**
     * Get ApiFilter settings set on Root Entity class level
     * @return array
     */
    private function getClassAnnotations(): array
    {
        $annotations = [];
        /** @var ApiFilter $annotation */
        foreach($this->reader->getClassAnnotations($this->targetEntity->getReflectionClass()) as $annotation) {

            if(is_a($annotation, ApiFilter::class, true)) {

                foreach($annotation->properties as $key => $value) {
                    $propertyAnnotation = clone $annotation;
                    $propertyAnnotation->properties = [];
                    $propertyAnnotation->id = \is_string($key) ? $key : $value;
                    $propertyAnnotation->strategy = \is_string($key) ? $value : null;
                    $annotations[] = $propertyAnnotation;
                }
            }
        }

        return $annotations;
    }

    /**
     * Get ApiFilter settings set on Root Entity properties level
     * @return array
     */
    private function getPropertyAnnotations(): array
    {
        $annotations = [];

        foreach($this->targetEntity->getReflectionProperties() as $property) {

            /** @var ApiFilter $annotation */
            foreach($this->reader->getPropertyAnnotations($property) as $annotation) {

                if(is_a($annotation, ApiFilter::class, true)) {
                    $annotation->id = $property->name;
                    $annotations[] = $annotation;
                }
            }
        }

        return $annotations;
    }

    private function createParameterCollectionFromParameterBag(ParameterBag $parameterBag)
    {
        return $this->parameterCollectionFactory->create($parameterBag);
    }
}