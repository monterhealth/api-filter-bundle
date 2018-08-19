<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/17/2018
 * Time: 10:07 PM
 */

namespace Monter\ApiFilterBundle\Parameter\Tests;

use Monter\ApiFilterBundle\Parameter\Command;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testValue(): void
    {
        $value = 'test-value';

        $command = new Command($value);

        $this->assertSame($value, $command->getValue());
    }

    public function testValidOperators(): void
    {
        $command = new Command('test');

        // default operator

        $this->assertTrue($command->hasOperator());

        $this->assertSame('EQUALS', $command->getOperator());

        $this->assertFalse($command->isNot());

        // valid operators

        $validOperators = [
            'gt' => 'GREATER_THAN',
            'lt' => 'LESS_THAN',
            'gte' => 'GREATER_THAN_EQUALS',
            'lte' => 'LESS_THAN_EQUALS',
            'after' => 'GREATER_THAN_EQUALS',
            'before' => 'LESS_THAN_EQUALS',
            'strictly_after' => 'GREATER_THAN',
            'strictly_before' => 'LESS_THAN',
            'equals' => 'EQUALS',
            'partial' => 'PARTIAL',
            'start' => 'START',
            'end' => 'END',
            'word_start' => 'WORD_START',
            'asc' => 'ASCENDING',
            'desc' => 'DESCENDING'
        ];

        foreach($validOperators as $operator => $type) {
            $command->setOperator($operator);

            $this->assertTrue($command->hasOperator());

            $this->assertSame($type, $command->getOperator());
        }

        // invalid operators

        $lastValidOperator = \end($validOperators);
        $invalidOperators = [
            'invalid',
            'omg',
            'word-start'
        ];
        foreach($invalidOperators as $operator) {
            $command->setOperator($operator);

            $this->assertTrue($command->hasOperator());

            $this->assertSame($lastValidOperator, $command->getOperator());
        }

        // not operator

        $this->assertFalse($command->isNot());

        $command->setOperator('not');

        $this->assertTrue($command->isNot());

        $this->assertSame($lastValidOperator, $command->getOperator());
    }
}