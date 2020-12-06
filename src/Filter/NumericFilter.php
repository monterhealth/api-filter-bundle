<?php

namespace MonterHealth\ApiFilterBundle\Filter;


use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

class NumericFilter extends AbstractFilter
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
        if(!\is_numeric($value)) {
            throw new InvalidValueException(sprintf('Invalid value used for query parameter \'%s\'.', $apiFilter->id));
        }

        // create response
        $target = $this->determineTarget($targetTableAlias, $apiFilter->id);

        $queryParameterName = $queryNameGenerator->generateParameterName($target);

        $operator = $command->isNot() ? '!=' : '=';
        $result =  sprintf('%s %s :%s', $target, $operator, $queryParameterName);

        $parameter->setUsed(true);

        return $this->createFilterResult($result, [$queryParameterName => $value]);
    }

    /**
     * @param $result
     * @param array $queryParameters
     * @return FilterResult
     */
    private function createFilterResult($result, array $queryParameters): FilterResult
    {
        return new FilterResult('constraint', $result, $queryParameters, $this->joins);
    }
}