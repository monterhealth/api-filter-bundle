<?php

namespace MonterHealth\ApiFilterBundle\Annotation;

/**
 * Creates ApiFilters from Annotations
 * Class ApiFilterFactory
 * @package MonterHealth\ApiFilterBundle\Annotation
 */
class ApiFilterFactory
{
    private Reader $reader;

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
        return $this->getAttributes($className, $annotationClass);
    }

    /**
     * @param string $className
     * @param string $annotationClass
     * @return array
     * @throws \ReflectionException
     */
    private function getAttributes(string $className, string $annotationClass): array
    {
        return array_merge(
            $this->getClassAttributes($className, $annotationClass),
            $this->getPropertyAttributes($className, $annotationClass)
        );
    }

    /**
     * Get ApiFilter settings set on Root Entity class level
     * @param string $className
     * @param string $annotationClass
     * @return array
     * @throws \ReflectionException
     */
    private function getClassAttributes(string $className, string $annotationClass): array
    {
        $filters = [];

        /** @var ApiFilter $filter */
        foreach($this->reader->getFilterAttributes(new \ReflectionClass($className)) as $filter) {

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
    private function getPropertyAttributes(string $className, string $annotationClass): array
    {
        $filters = [];

        $reflectionClass = new \ReflectionClass($className);

        foreach($reflectionClass->getProperties() as $property) {

            /** @var ApiFilter $filter */
            foreach($this->reader->getFilterAttributes($property) as $filter) {

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