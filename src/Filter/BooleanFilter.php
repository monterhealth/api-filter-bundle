<?php

namespace MonterHealth\ApiFilterBundle\Filter;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

class BooleanFilter extends AbstractFilter
{
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, QueryNameGeneratorInterface $queryNameGenerator, array $configs = []): ?FilterResult
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
        $target = $this->determineTarget($targetTableAlias, $apiFilter->id);
        $result =  sprintf('%s=%b', $target, $value);

        $parameter->setUsed(true);

        return $this->createFilterResult($result);
    }

    /**
     * @param $result
     * @return FilterResult
     */
    private function createFilterResult($result): FilterResult
    {
        return new FilterResult('constraint', $result, [], $this->joins);
    }
}