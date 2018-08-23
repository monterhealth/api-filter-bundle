<?php

namespace Monter\ApiFilterBundle\Parameter\Factory;


use Monter\ApiFilterBundle\Parameter\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ParameterCollectionFactory
{
    /**
     * @param ParameterBag $parameterBag
     * @return Collection
     */
    public function create(ParameterBag $parameterBag): Collection;

}