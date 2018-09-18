<?php

namespace MonterHealth\ApiFilterBundle\Tests\Filter;


use MonterHealth\ApiFilterBundle\Filter\FilterResult;
use PHPUnit\Framework\TestCase;

class FilterResultTest extends TestCase
{
    public function testGetType(): void
    {
        $type = 'constraint';

        $filterResult = new FilterResult($type, '');

        $this->assertSame($type, $filterResult->getType());
    }

    public function testGetResult(): void
    {
        $result = 'table.parameter=1';

        $filterResult = new FilterResult('', $result);

        $this->assertSame($result, $filterResult->getResult());
    }

    public function testGetSettings(): void
    {
        $settings = ['ascending' => true];

        $filterResult = new FilterResult('type', 'result', [], [], $settings);

        $this->assertSame($settings, $filterResult->getSettings());
    }

    public function testGetSetting(): void
    {
        $settings = [
            'ascending' => true,
            'sequence' => 2
        ];

        $filterResult = new FilterResult('type', 'result', [], [], $settings);

        foreach($settings as $setting => $value) {
            $this->assertSame($value, $filterResult->getSetting($setting));
        }
    }

    public function testGetJoins(): void
    {
        $joins = ['joinTable1', 'joinTable2'];

        $filterResult = new FilterResult('type', 'result', [], $joins, []);

        $this->assertSame($joins, $filterResult->getJoins());
    }

    public function testGetQueryParameters(): void
    {
        $queryParameters = [
            'p1' => 'elephant',
            'p2' => 9000
        ];

        $filterResult = new FilterResult('type', 'result', $queryParameters, [], []);

        $this->assertSame($queryParameters, $filterResult->getQueryParameters());
    }
}