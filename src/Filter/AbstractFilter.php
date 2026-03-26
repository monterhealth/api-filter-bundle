<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;

abstract class AbstractFilter implements Filter
{
    protected $joins = [];

    /**
     * Shared filter services must clear join bookkeeping on every apply so the state does not leak across requests.
     */
    protected function resetJoins(): void
    {
        $this->joins = [];
    }

    /**
     * @param string $targetTableAlias
     * @param string $property
     * @param ClassMetadata|null $classMetadata Used to decide whether the first nested segment is an embedded class.
     * @return string
     */
    protected function determineTarget(string $targetTableAlias, string $property, ?ClassMetadata $classMetadata = null): string
    {

        if($this->isPropertyNested($property)) {

            $firstSegment = explode('.', $property)[0];
            if(null !== $classMetadata && isset($classMetadata->embeddedClasses[$firstSegment])) {
                // Embedded properties live on the same table; no join is required.
                return $targetTableAlias . '.' . $property;
            }

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
    protected function extractTargetFromProperty(string $property): string
    {
        if(!strpos($property, '.')) {
            return $property;
        }
        $parts = array_reverse(explode('.', $property));
        return $parts[1].'.'.$parts[0];
    }
}