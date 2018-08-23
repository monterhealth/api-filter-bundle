<?php

namespace Monter\ApiFilterBundle\Filter;

/**
 * Order Filter Interface
 * Interface Order
 * @package Monter\ApiFilterBundle\Filter
 */
interface Order
{
    public function getStrategy(): string;

    /**
     * @param string $strategy
     * @throws \RuntimeException
     */
    public function setStrategy(string $strategy): void;

    public function isAscending(): bool;

    public function getSequence(): int;
}