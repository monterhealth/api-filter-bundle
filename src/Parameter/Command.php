<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/7/2018
 * Time: 9:50 PM
 */

namespace Monter\ApiFilterBundle\Parameter;

/**
 * Stores the command part of a query parameter request
 *
 * Class Command
 * @package Monter\ApiFilterBundle\Parameter
 */
class Command
{
    public const OPERATOR_TYPES = [
        'gt' => 'GREATER_THAN',
        'lt' => 'LESS_THEN',
        'gte' => 'GREATER_THAN_EQUALS',
        'lte' => 'LESS_THAN_EQUALS',
        'after' => 'GREATER_THAN_EQUALS',
        'before' => 'LESS_THAN_EQUALS',
        'strictly_after' => 'GREATER_THAN',
        'strictly_before' => 'LESS_THEN',
        'equals' => 'EQUALS',
        'partial' => 'PARTIAL',
        'start' => 'START',
        'end' => 'END',
        'word_start' => 'WORD_START',
        'asc' => 'ASCENDING',
        'desc' => 'DESCENDING'
    ];

    public const OR_OPERATOR = 'or';

    public const NOT_OPERATOR = 'not';

    /**
     * @var string
     */
    private $value;

    /**
     * AND / OR
     * @var bool
     */
    private $or = false;

    /**
     * NOT
     * @var bool
     */
    private $not = false;

    private $operator = 'EQUALS';

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isOr(): bool
    {
        return $this->or;
    }

    public function isNot(): bool
    {
        return $this->not;
    }

    /**
     * @param string $operator
     * @return Command
     */
    public function setOperator(string $operator): self
    {
        if(array_key_exists($operator, self::OPERATOR_TYPES)) {
            $this->operator = self::OPERATOR_TYPES[$operator];

        } elseif(self::OR_OPERATOR === $operator) {
            $this->or = true;

        } elseif(self::NOT_OPERATOR === $operator) {
            $this->not = true;
        }

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function hasOperator(): bool
    {
        return $this->operator ? true : false;
    }
}