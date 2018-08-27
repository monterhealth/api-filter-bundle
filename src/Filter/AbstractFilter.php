<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;

abstract class AbstractFilter implements Filter
{

    /**
     * @param string $targetTableAlias
     * @param ApiFilter $apiFilter
     * @return string
     */
    protected function determineTarget(string $targetTableAlias, ApiFilter $apiFilter): string
    {
        $parameter = $apiFilter->id;

        return $this->isPropertyNested($parameter) ? $parameter : $targetTableAlias.'.'.$parameter;
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
}