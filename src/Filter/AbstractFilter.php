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

            $target = $property;

            $this->joins[] = $this->getJoinTableAliasFromProperty($property);

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

    protected function getJoinTableAliasFromProperty($property): ?string
    {
        $joinTable = null;

        if(strpos($property, '.')) {
            list($joinTable) = explode('.', $property);
        }

        return $joinTable;
    }
}