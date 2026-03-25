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
        if (!isset($all[$prefix]) || !\is_array($all[$prefix])) {
            return ['groups' => [], 'globals' => $query];
        }

        $nested = $all[$prefix];
        unset($all[$prefix]);

        if ($nested !== []) {
            ksort($nested, SORT_NATURAL);
        }

        $groups = [];
        foreach ($nested as $groupPayload) {
            if (!\is_array($groupPayload)) {
                continue;
            }
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
            $name = str_replace(':', '.', (string) $name);
            if (!\is_array($commands)) {
                $out[$name] = $commands;

                continue;
            }
            if (\is_string(key($commands))) {
                $out[$name] = [$commands];

                continue;
            }
            $out[$name] = $commands;
        }

        return $out;
    }
}
