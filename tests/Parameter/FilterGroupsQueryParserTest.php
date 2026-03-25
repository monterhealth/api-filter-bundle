<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Parameter;

use MonterHealth\ApiFilterBundle\Parameter\FilterGroupsQueryParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class FilterGroupsQueryParserTest extends TestCase
{
    public function testReturnsGlobalsOnlyWhenPrefixAbsent(): void
    {
        $query = new ParameterBag([
            'title' => [['partial' => 'x']],
            'order' => [['desc' => 'pages']],
        ]);
        $split = FilterGroupsQueryParser::splitQueryBag($query);

        self::assertSame([], $split['groups']);
        self::assertSame($query, $split['globals']);
    }

    public function testExtractsOrderedGroupsAndGlobals(): void
    {
        $query = new ParameterBag([
            'mh_groups' => [
                1 => ['title' => ['partial' => 'B']],
                0 => ['pages' => ['gte' => '300']],
            ],
            'order' => [['asc' => 'title']],
        ]);
        $split = FilterGroupsQueryParser::splitQueryBag($query);

        self::assertCount(2, $split['groups']);
        self::assertSame(['pages' => [['gte' => '300']]], $split['groups'][0]->all());
        self::assertSame(['title' => [['partial' => 'B']]], $split['groups'][1]->all());
        self::assertSame(['order' => [['asc' => 'title']]], $split['globals']->all());
    }

    public function testSkipsNonArrayGroupPayload(): void
    {
        $query = new ParameterBag([
            'mh_groups' => [
                0 => ['title' => ['partial' => 'ok']],
                1 => 'broken',
            ],
        ]);
        $split = FilterGroupsQueryParser::splitQueryBag($query);

        self::assertCount(1, $split['groups']);
    }

    public function testCustomPrefix(): void
    {
        $query = new ParameterBag([
            'my_groups' => [
                0 => ['title' => ['equals' => 't']],
            ],
            'other' => '1',
        ]);
        $split = FilterGroupsQueryParser::splitQueryBag($query, 'my_groups');

        self::assertCount(1, $split['groups']);
        self::assertSame(['other' => '1'], $split['globals']->all());
    }
}