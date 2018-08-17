<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/9/2018
 * Time: 10:25 PM
 */

namespace Monter\ApiFilterBundle\Filter;


use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;

class SearchFilter implements Filter
{
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, array $configs = []): string
    {
        $response = [];

        $parameter = $parameterCollection->getUnusedByName($apiFilter->id);
        if(null === $parameter) {
            return '';
        }

        /** @var Command $command */
        foreach($parameter->getCommands() as $command) {
            switch ($command->getOperator()) {
                case 'EQUALS':
                    $operator = $command->isNot() ? '!=' : '=';
                    $response[] = sprintf('%s.%s%s\'%s\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    break;
                case 'PARTIAL':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $response[] = sprintf('%s.%s %s \'%%%s%%\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    break;
                case 'START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $response[] = sprintf('%s.%s %s \'%s%%\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    break;
                case 'END':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    $response[] = sprintf('%s.%s %s \'%%%s\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    break;
                case 'WORD_START':
                    $operator = $command->isNot() ? 'NOT LIKE' : 'LIKE';
                    // first word
                    $str = sprintf('%s.%s %s \'%s%%\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    $str .= $command->isNot() ? ' AND ' : ' OR ';
                    // other words
                    $str .= sprintf('%s.%s %s \'%% %s%%\'', $targetTableAlias, $apiFilter->id, $operator, $command->getValue());
                    $response[] = $str;
                    break;
            }
        }

        $parameter->setUsed(true);

        return \count($response) ? '('. implode(') AND (', $response). ')' : '';
    }
}