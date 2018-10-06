<?php

namespace MonterHealth\ApiFilterBundle\Filter;

use MonterHealth\ApiFilterBundle\Annotation\ApiFilter;
use MonterHealth\ApiFilterBundle\InvalidValueException;
use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Util\QueryNameGeneratorInterface;

class OrderFilter extends AbstractFilter implements Order
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
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param array $configs
     * @return FilterResult|null
     */
    public function apply(string $targetTableAlias, Collection $parameterCollection, ApiFilter $apiFilter, QueryNameGeneratorInterface $queryNameGenerator, array $configs = []): ?FilterResult
    {
        $result = null;
        // find matching parameter based on orderParameterName
        $orderParameterName = $configs['order_parameter_name'] ?? null;
        if(null === $orderParameterName) {
            return null;
        }
        $parameter = $parameterCollection->getUnusedByName($orderParameterName);
        if(null === $parameter) {
            return null;
        }

        // filter column names
        // look for a match between a command value of the order parameter and the apiFilter id
        /** @var Command $command */
        foreach($parameter->getCommands() as $sequence => $command) {
            if(\strtolower($command->getValue()) === \strtolower($apiFilter->id)) {

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
                $result = $this->determineTarget($targetTableAlias, $apiFilter->id);

                break;
            }

        }

        return $result ? $this->createFilterResult($result) : null;
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

    /**
     * @param $result
     * @return FilterResult
     */
    private function createFilterResult($result): FilterResult
    {
        return new FilterResult('order', $result, [], $this->joins, ['ascending' => $this->isAscending(), 'sequence' => $this->getSequence()]);
    }
}