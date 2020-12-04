<?php

namespace MonterHealth\ApiFilterBundle\Annotation;

/**
 * Creates ApiFilters from Annotations
 * Class ApiFilterFactory
 * @package MonterHealth\ApiFilterBundle\Annotation
 */
class ApiFilterFactory
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $className
     * @param string $annotationClass
     * @throws \ReflectionException
     * @return array
     */
    public function create(string $className, string $annotationClass = ApiFilter::class): array
    {
        return $this->getAnnotations($className, $annotationClass);
    }

    /**
     * @param string $className
     * @param string $annotationClass
     * @return array
     * @throws \ReflectionException
     */
    private function getAnnotations(string $className, string $annotationClass): array
    {
        return array_merge(
            $this->getClassAnnotations($className, $annotationClass),
            $this->getPropertyAnnotations($className, $annotationClass)
        );
    }

    /**
     * Get ApiFilter settings set on Root Entity class level
     * @param string $className
     * @param string $annotationClass
     * @return array
     * @throws \ReflectionException
     */
    private function getClassAnnotations(string $className, string $annotationClass): array
    {
        $filters = [];
        /** @var ApiFilter $filter */
        foreach($this->reader->getClassAnnotations(new \ReflectionClass($className)) as $filter) {

            if(is_a($filter, $annotationClass, true)) {

                foreach($filter->properties as $key => $value) {
                    $propertyFilter = clone $filter;
                    $propertyFilter->properties = [];
                    $propertyFilter->id = \is_string($key) ? $key : $value;
                    $propertyFilter->strategy = \is_string($key) ? $value : null;
                    $filters[] = $propertyFilter;
                }
            }
        }

        return $filters;
    }


    /**
     * Get ApiFilter settings set on Root Entity properties level
     * @param string $className
     * @param string $annotationClass
     * @throws \ReflectionException
     * @return array
     */
    private function getPropertyAnnotations(string $className, string $annotationClass): array
    {
        $filters = [];

        $reflectionClass = new \ReflectionClass($className);

        foreach($reflectionClass->getProperties() as $property) {

            /** @var ApiFilter $filter */
            foreach($this->reader->getPropertyAnnotations($property) as $filter) {

                if(is_a($filter, $annotationClass, true)) {
                    $filter->id = $property->name;
                    if(1 === \count($filter->properties) && isset($filter->properties[0])) {
                        $filter->strategy = $filter->properties[0];
                        $filter->properties = [];
                    }
                    $filters[] = $filter;
                }
            }
        }

        return $filters;
    }
}