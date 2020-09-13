<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

class SearchFilter extends AbstractFilter
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
        if(null === $parameter) {
            return null;
        }

        $target = $this->determineTarget($targetTableAlias, $apiFilter->id);

        $queryParameters = [];

        /** @var Command $command */
        foreach($parameter->getCommands() as $command) {

            $queryParameterName = $queryNameGenerator->generateParameterName($target);

            switch ($command->getOperator()) {

                case 'EQUALS':
                    $operator = $command->isNot() ? '!=' : '=';
                    $constraint[] = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = $command->getValue();
                    break;

                case 'PARTIAL':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = sprintf('%%%s%%', $command->getValue());
                    break;

                case 'START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = sprintf('%s%%', $command->getValue());
                    break;

                case 'END':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = sprintf('%%%s', $command->getValue());
                    break;

                case 'WORD_START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';

                    // first word
                    $str = sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = sprintf('%s%%', $command->getValue());

                    $str .= $command->isNot() ? ' AND ' : ' OR ';

                    // other words
                    $queryParameterName = $queryNameGenerator->generateParameterName($target); // new name required
                    $str .= sprintf('%s %s :%s', $target, $operator, $queryParameterName);
                    $queryParameters[$queryParameterName] = sprintf('%% %s%%', $command->getValue());

                    $constraint[] = $str;
                    break;

                case 'IN':
                    $queryInParts = [];
                    // break up value and add each piece to the IN("a", "b", "c") query
                    foreach(explode(Command::VALUE_SEPARATOR, $command->getValue()) as $num => $value) {
                        $paramName = sprintf('%s_%s', $queryParameterName, $num);
                        $queryInParts[] = sprintf(':%s', $paramName);
                        $queryParameters[$paramName] = $value;
                    }
                    $queryIn = implode(', ', $queryInParts);
                    $operator = $command->isNot() ? 'NOT' : '';
                    $constraint[] = sprintf('%s %s IN(%s)', $target, $operator, $queryIn);
                    break;
            }
        }

        $parameter->setUsed(true);

        $result = \count($constraint) ? '('. implode(') AND (', $constraint). ')' : null;

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