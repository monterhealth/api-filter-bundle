<?php

namespace MonterHealth\ApiFilterBundle\Filter;

abstract class AbstractFilter implements Filter
{
    protected $joins = [];

    /**
     * @param string $targetTableAlias
     * @param string $property
     * @return string
     */
    protected function determineTarget(string $targetTableAlias, string $property): string
    {

        if($this->isPropertyNested($property)) {

            $target = $this->extractTargetFromProperty($property);

            $this->extractJoinsFromProperty($property);

        } else {

            $target = $targetTableAlias.'.'.$property;
        }

        return $target;
    }

    /**
     * @param $property
     * @return bool
     */
    protected function isPropertyNested($property): bool
    {
        $pos = strpos($property, '.');
        return (false !== $pos);
    }

    protected function extractJoinsFromProperty(string $property): void
    {
        if(!strpos($property, '.')) { return; }
        $joins = explode('.', $property);
        // remove last = property
        array_pop($joins);

        $previous = null;
        foreach($joins as $join) {
            if($previous)  {
                $this->joins[$previous] = $join;
            } else {
                $this->joins[] = $join;
            }
            $previous = $join;
        }
    }

    /**
     * Correct for nested joins: target must only be the last part
     * rootAlias.nestedAlias.parameterName >> nestedAlias.parameterName
     * @param string $property
     * @return string
     */
    protected function extractTargetFromProperty(string $property) {
        if(!strpos($property, '.')) {
            return $property;
        }
        $parts = array_reverse(explode('.', $property));
        return $parts[1].'.'.$parts[0];
    }
}