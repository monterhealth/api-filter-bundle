<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

interface Filter
{
	public function apply(string                      $targetTableAlias,
	                      Collection                  $parameterCollection,
	                      ApiFilter                   $apiFilter,
	                      QueryNameGeneratorInterface $queryNameGenerator,
	                      array                       $configs = [],
	                      array                       $embedded = []
	): ?FilterResult;
}