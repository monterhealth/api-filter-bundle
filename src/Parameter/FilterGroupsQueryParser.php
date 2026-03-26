<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Parameter;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Extracts nested mh_groups[…] query parameters into separate ParameterBag instances per group.
 *
 * All other keys remain in the "globals" bag (ordering, filters that apply once, etc.).
 */
final class FilterGroupsQueryParser
{
    public const DEFAULT_PREFIX = 'mh_groups';

    /**
     * @return array{groups: list<ParameterBag>, globals: ParameterBag}
     */
    public static function splitQueryBag(ParameterBag $query, string $prefix = self::DEFAULT_PREFIX): array
    {
        $all = $query->all();
        // Fast path: no grouped key present, keep original query as globals and skip allocations.
        if (!isset($all[$prefix]) || !\is_array($all[$prefix])) {
            return ['groups' => [], 'globals' => $query];
        }

        // Isolate grouped payload and keep all non-group query params in globals.
        $nested = $all[$prefix];
        unset($all[$prefix]);

        // Keep deterministic processing order for group indexes (0,1,2... or stringable numerics).
        if ($nested !== []) {
            ksort($nested, SORT_NATURAL);
        }

        $groups = [];
        foreach ($nested as $groupPayload) {
            // Ignore malformed/non-array groups instead of failing the entire request.
            if (!\is_array($groupPayload)) {
                continue;
            }
            // Normalize shape so downstream parsing matches regular query parsing semantics.
            $groups[] = new ParameterBag(self::normalizeGroupPayload($groupPayload));
        }

        return ['groups' => $groups, 'globals' => new ParameterBag($all)];
    }

    /**
     * Ensure leaf command values remain compatible with {@see Factory\DefaultParameterCollectionFactory}.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private static function normalizeGroupPayload(array $payload): array
    {
        $out = [];
        foreach ($payload as $name => $commands) {
            // Keep nested-property aliasing consistent with normal query parsing (author:name => author.name).
            $name = str_replace(':', '.', (string) $name);
            if (!\is_array($commands)) {
                // Scalar form: parameter=value
                $out[$name] = $commands;

                continue;
            }
            if (\is_string(key($commands))) {
                // Single-command associative form: parameter[strategy]=value
                $out[$name] = [$commands];

                continue;
            }
            // Already in list form: parameter[][strategy]=value
            $out[$name] = $commands;
        }

        return $out;
    }
}
