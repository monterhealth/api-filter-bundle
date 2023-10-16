<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

class DateFilter extends AbstractFilter
{
    /**
     * @param string $targetTableAlias
     * @param Collection $parameterCollection
     * @param ApiFilter $apiFilter
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param array $configs
     * @return FilterResult|null
     */
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, QueryNameGeneratorInterface $queryNameGenerator, array $configs = []): ?FilterResult
    {
        $constraint = [];

        $parameter = $parameterCollection->getUnusedByName($apiFilter->id);
        if (null === $parameter) {
            return null;
        }
        $target = $this->determineTarget($targetTableAlias, $apiFilter->id);

        $queryParameters = [];

        /** @var Command $command */
        foreach ($parameter->getCommands() as $command) {

            // need to enhance this logic
            $value = $command->getValue();
            try {
                $value = new \DateTime($value); // currently \DateTime only, need help on \DateTimeImmutable
            } catch (\Exception $e) {
                // sounds funny catch exception and throw a new one inside, but this will work for now.
                throw new InvalidValueException(sprintf('Invalid value used for query parameter \'%s\'.', $apiFilter->id));
            }

            $queryParameterName = $queryNameGenerator->generateParameterName($target);

            switch ($command->getOperator()) {
                case 'EQUALS':
                    $operator = $command->isNot() ? '!=' : '=';
                    $constraint[] = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'GREATER_THAN':
                    $constraint[] = sprintf('%s %s :%s', $target, '>', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'LESS_THAN':
                    $constraint[] = sprintf('%s %s :%s', $target, '<', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'GREATER_THAN_EQUALS':
                    $constraint[] = sprintf('%s %s :%s', $target, '>=', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'LESS_THAN_EQUALS':
                    $constraint[] = sprintf('%s %s :%s', $target, '<=', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'NULL_OR_GREATER_THAN':
                    $constraint[] = sprintf('%s IS NULL OR %s %s :%s', $target, $target, '>', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'NULL_OR_LESS_THAN':
                    $constraint[] = sprintf('%s IS NULL OR %s %s :%s', $target, $target, '<', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'NULL_OR_GREATER_THAN_EQUALS':
                    $constraint[] = sprintf('%s IS NULL OR %s %s :%s', $target, $target, '>=', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;

                case 'NULL_OR_LESS_THAN_EQUALS':
                    $constraint[] = sprintf('%s IS NULL OR %s %s :%s', $target, $target, '<=', $queryParameterName);
                    $queryParameters[$queryParameterName] = $value;
                    break;
            }
        }

        $parameter->setUsed(true);

        $result = \count($constraint) ? '(' . implode(') AND (', $constraint) . ')' : null;

        return $result ? $this->createFilterResult($result, $queryParameters) : null;
    }

    /**
     * @param $result
     * @param array $queryParameters
     * @return FilterResult
     */
    private function createFilterResult($result, array $queryParameters): FilterResult
    {
        return new FilterResult('constraint', $result, $queryParameters, $this->joins, []);
    }
}
