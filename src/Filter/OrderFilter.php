<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/9/2018
 * Time: 10:25 PM
 */

namespace Monter\ApiFilterBundle\Filter;


use Monter\ApiFilterBundle\Annotation\ApiFilter;
use Monter\ApiFilterBundle\InvalidValueException;
use Monter\ApiFilterBundle\MonterApiFilter;
use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;

class OrderFilter implements Filter, Order
{
    public const ASCENDING = 'ASCENDING';
    public const DESCENDING = 'DESCENDING';

    private const STRATEGIES = [
        'ASCENDING',
        'DESCENDING',
    ];

    /**
     * Strategy: ASC or DESC
     * @var string
     */
    private $strategy = self::ASCENDING;

    /**
     * Sequence of parameter in query
     * Important for correct ordering
     * @var int
     */
    private $sequence = 0;

    /**
     * @param string $targetTableAlias
     * @param Collection $parameterCollection
     * @param ApiFilter $apiFilter
     * @param array $configs
     * @return string
     */
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, array $configs = []): string
    {
        $response = '';

        // find matching parameter based on orderParameterName
        $orderParameterName = $configs['order_parameter_name'] ?? null;
        if(null === $orderParameterName) {
            return $response;
        }

        $parameter = $parameterCollection->getUnusedByName($orderParameterName);
        if(null === $parameter) {
            return $response;
        }

        // filter column names are
        /** @var Command $command */
        foreach($parameter->getCommands() as $sequence => $command) {

            if($command->getValue() === $apiFilter->id) {

                // store sequence
                $this->sequence = $sequence;

                // define strategy
                if(\in_array($command->getOperator(), self::STRATEGIES, true)) {
                    // defined by user
                    $this->setStrategy($command->getOperator());
                } elseif(\in_array($apiFilter->strategy, self::STRATEGIES, true)) {
                    // defined on entity
                    $this->setStrategy($apiFilter->strategy);
                } else {
                    // defined in configuration
                    $strategy = $configs['default_order_strategy'] ?? 'ascending';
                    $this->setStrategy(strtoupper($strategy));
                }

                // create response
                $response =  sprintf('%s.%s', $targetTableAlias, $command->getValue());

                break;
            }

        }

        return $response;
    }

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param string $strategy
     * @throws \RuntimeException
     */
    public function setStrategy(string $strategy): void
    {
        if(!\in_array($strategy, self::STRATEGIES, true)) {
            throw new InvalidValueException(sprintf('Invalid order strategy \'%s\' set.', $strategy));
        }
        $this->strategy = $strategy;
    }

    public function isAscending(): bool
    {
        return self::ASCENDING === $this->strategy;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }
}