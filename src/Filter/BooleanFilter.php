<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/9/2018
 * Time: 10:25 PM
 */

namespace Monter\ApiFilterBundle\Filter;


use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\InvalidValueException;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;

class BooleanFilter implements Filter
{
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, array $configs = []): ?FilterResult
    {
        $result = null;

        $parameter = $parameterCollection->getUnusedByName($apiFilter->id);
        if(null === $parameter) {
            return null;
        }

        /** @var Command $command */
        $command = $parameter->getFirstCommand();

        // define value
        $value = $command->getValue();
        if($value === 'true' || $value === true || $value === '1' || $value === 1) {
            $value = 1;
        } else if($value === 'false' || $value === false || $value === '0' || $value === 0) {
            $value = 0;
        } else {
            throw new InvalidValueException(sprintf('Invalid value used for query parameter \'%s\'.', $apiFilter->id));
        }

        // create response
        $result =  sprintf('%s.%s=%b', $targetTableAlias, $apiFilter->id, $value);
        $parameter->setUsed(true);

        return $this->createFilterResult($result);
    }

    /**
     * @param $result
     * @return FilterResult
     */
    private function createFilterResult($result): FilterResult
    {
        return new FilterResult('constraint', $result);
    }
}