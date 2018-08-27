<?php

namespace MonterHealth\ApiFilterBundle\Parameter\Factory;


use MonterHealth\ApiFilterBundle\Parameter\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ParameterCollectionFactory
{
    /**
     * @param ParameterBag $parameterBag
     * @return Collection
     */
    public function create(ParameterBag $parameterBag): Collection;

}