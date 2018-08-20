<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/9/2018
 * Time: 10:14 PM
 */

namespace Monter\ApiFilterBundle\Filter;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Parameter\Collection;

interface Filter
{
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, array $configs = []): ?FilterResult;
}