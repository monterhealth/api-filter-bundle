<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Parameter\Factory;

/**
 * Shared query-shape normalization used by parameter factories.
 */
final class ParameterInputNormalizer
{
    /**
     * Normalize nested-property aliases: author:name => author.name.
     */
    public static function normalizeParameterName(string $name): string
    {
        return str_replace(':', '.', $name);
    }

    /**
     * Normalize query command shapes so factories can iterate consistently:
     * - scalar: parameter=value               => [value]
     * - single associative: parameter[strategy]=value => [[strategy => value]]
     * - list form: parameter[][strategy]=value        => unchanged
     *
     * @param mixed $commands
     *
     * @return array<int, mixed>
     */
    public static function normalizeCommandList($commands): array
    {
        if (!\is_array($commands)) {
            return [$commands];
        }

        if (\is_string(\key($commands))) {
            return [$commands];
        }

        return $commands;
    }
}
