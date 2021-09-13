<?php

namespace MonterHealth\ApiFilterBundle\Parameter;

/**
 * Stores the command part of a query parameter request
 *
 * Class Command
 * @package MonterHealth\ApiFilterBundle\Parameter
 */
class Command
{
    public const OPERATOR_TYPES = [
        'gt' => 'GREATER_THAN',
        'lt' => 'LESS_THAN',
        'gte' => 'GREATER_THAN_EQUALS',
        'lte' => 'LESS_THAN_EQUALS',
        'bt' => 'BETWEEN',
        'between' => 'BETWEEN',
        'after' => 'GREATER_THAN_EQUALS',
        'before' => 'LESS_THAN_EQUALS',
        'strictly_after' => 'GREATER_THAN',
        'strictly_before' => 'LESS_THAN',
        'null_or_after' => 'NULL_OR_GREATER_THAN_EQUALS',
        'null_or_before' => 'NULL_OR_LESS_THAN_EQUALS',
        'null_or_strictly_after' => 'NULL_OR_GREATER_THAN',
        'null_or_strictly_before' => 'NULL_OR_LESS_THAN',
        'equals' => 'EQUALS',
        'partial' => 'PARTIAL',
        'in' => 'IN',
        'start' => 'START',
        'end' => 'END',
        'word_start' => 'WORD_START',
        'asc' => 'ASCENDING',
        'desc' => 'DESCENDING',
    ];

// TODO: Add a useful or implementation (like or_start and or_end? or only within a parameter?)
//    public const OR_OPERATOR = 'or';

    public const NOT_OPERATOR = 'not';

    public const VALUE_SEPARATOR = '|';

    /**
     * @var string
     */
    private $value;

//    /**
//     * AND / OR
//     * @var bool
//     */
//    private $or = false;

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

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

//    public function isOr(): bool
//    {
//        return $this->or;
//    }

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

//        } elseif(self::OR_OPERATOR === $operator) {
//            $this->or = true;
//
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