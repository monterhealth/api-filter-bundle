<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
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

            echo $command->getOperator();
            switch ($command->getOperator()) {
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

                // case 'BETWEEN':
                //     $queryInParts = [];
                //     // break up value
                //     $valueArray = explode(Command::VALUE_SEPARATOR, $command->getValue());

                //     if (\count($valueArray) < 2 || \count($valueArray) > 2) {
                //         throw new InvalidValueException(sprintf('Invalid value used for query parameter \'%s\'.', $apiFilter->id));
                //     }

                //     foreach ($valueArray as $num => $value) {
                //         $paramName = sprintf('%s_%s', $queryParameterName, $num);
                //         $queryInParts[] = sprintf(':%s', $paramName);
                //         $queryParameters[$paramName] = $value;
                //     }

                //     $constraint[] = sprintf('%s >= %s AND %s <= %s', $target, $queryInParts[0], $target, $queryInParts[1]);
                //     break;
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
