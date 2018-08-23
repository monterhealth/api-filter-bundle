<?php

namespace Monter\ApiFilterBundle\Filter;

use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;

class SearchFilter extends AbstractFilter
{
    /**
     * @param string $targetTableAlias
     * @param Collection $parameterCollection
     * @param ApiFilter $apiFilter
     * @param array $configs
     * @return FilterResult|null
     */
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, array $configs = []): ?FilterResult
    {
        $constraint = [];

        $parameter = $parameterCollection->getUnusedByName($apiFilter->id);
        if(null === $parameter) {
            return null;
        }

        $target = $this->determineTarget($targetTableAlias, $apiFilter);

        /** @var Command $command */
        foreach($parameter->getCommands() as $command) {
            switch ($command->getOperator()) {
                case 'EQUALS':
                    $operator = $command->isNot() ? '!=' : '=';
                    $constraint[] = sprintf('%s%s\'%s\'', $target, $operator, $command->getValue());
                    break;
                case 'PARTIAL':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s \'%%%s%%\'', $target, $operator, $command->getValue());
                    break;
                case 'START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s \'%s%%\'', $target, $operator, $command->getValue());
                    break;
                case 'END':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $constraint[] = sprintf('%s %s \'%%%s\'', $target, $operator, $command->getValue());
                    break;
                case 'WORD_START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    // first word
                    $str = sprintf('%s %s \'%s%%\'', $target, $operator, $command->getValue());
                    $str .= $command->isNot() ? ' AND ' : ' OR ';
                    // other words
                    $str .= sprintf('%s %s \'%% %s%%\'', $target, $operator, $command->getValue());
                    $constraint[] = $str;
                    break;
            }
        }

        $parameter->setUsed(true);

        $result = \count($constraint) ? '('. implode(') AND (', $constraint). ')' : null;

        return $result ? $this->createFilterResult($result) : null;
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