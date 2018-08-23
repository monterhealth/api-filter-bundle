<?php

namespace Monter\ApiFilterBundle\Tests\Parameter\Factory;


use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Command;
use Monter\ApiFilterBundle\Parameter\Factory\DefaultParameterCollectionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class DefaultParameterCollectionFactoryTest extends TestCase
{
    /**
     * @var DefaultParameterCollectionFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new DefaultParameterCollectionFactory();
    }

    public function testCreateWithEmptyParameterBag(): void
    {
        $parameterBag = new ParameterBag();

        $collection = $this->factory->create($parameterBag);

        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEmpty($collection);
    }

    /**
     * @dataProvider getScenarios
     */
    public function testCreateScenarios(array $query, array $expectedParameters): void
    {
        $parameterBag = new ParameterBag($query);

        $collection = $this->factory->create($parameterBag);

        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertNotEmpty($collection);

        foreach($expectedParameters as $expectedParameter) {
            $parameter = $collection->getUnusedByName($expectedParameter['name']);

            $this->assertNotNull($parameter, sprintf('Assert that parameter `%s` is in the collection.', $expectedParameter['name']));

            $commands = $parameter->getCommands();

            foreach($expectedParameter['commands'] as $expectedCommand) {
                /** @var Command $command */
                $command = \array_shift($commands);

                $this->assertNotNull($command, sprintf('Assert that the parameter has the command `%s`.', $expectedCommand['value']));

                $this->assertSame($expectedCommand['value'], $command->getValue(), 'Testing the value.');

                $this->assertSame($expectedCommand['operator'], $command->getOperator(), 'Testing the operator.');

                $this->assertSame($expectedCommand['not'], $command->isNot(), 'Testing the \'not\' operator.');
            }
        }
    }

    public function getScenarios(): array
    {
        return [
            // parameter=value
            [
                // query
                ['param' => 'test'],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'EQUALS',
                                'not' => false,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[]=value
            [
                // query
                ['param' => ['test']],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'EQUALS',
                                'not' => false,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[operator]=value
            [
                // query
                ['param' => ['partial' => 'test']],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'PARTIAL',
                                'not' => false,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[][operator]=value
            [
                // query
                ['param' => [['word_start' => 'test']]],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'WORD_START',
                                'not' => false,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[operator][not]=value
            [
                // query
                ['param' => ['start' => ['not' => 'test']]],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'START',
                                'not' => true,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[][operator][not]=value
            [
                // query
                ['param' => [['start' => ['not' => 'test']]]],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'test',
                                'operator' => 'START',
                                'not' => true,
                            ]
                        ]
                    ]
                ]
            ],
            // parameter[]=value1&parameter[]=value2
            [
                // query
                ['param' => ['value1', 'value2']],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'value1',
                                'operator' => 'EQUALS',
                                'not' => false,
                            ],
                            [
                                'value' => 'value2',
                                'operator' => 'EQUALS',
                                'not' => false,
                            ],
                        ]
                    ]
                ]
            ],
            // parameter[][operator1]=value1&parameter[][operator2]=value2
            [
                // query
                ['param' => [['start' => 'value1'], ['end' => 'value2']]],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'value1',
                                'operator' => 'START',
                                'not' => false,
                            ],
                            [
                                'value' => 'value2',
                                'operator' => 'END',
                                'not' => false,
                            ],
                        ]
                    ]
                ]
            ],
            // parameter[][operator1][not]=value1&parameter[][operator2]=value2
            [
                // query
                ['param' => [['start' => ['not' => 'value1']], ['end' => 'value2']]],
                // expected parameters
                [
                    [
                        'name' => 'param',
                        'commands' => [
                            [
                                'value' => 'value1',
                                'operator' => 'START',
                                'not' => true,
                            ],
                            [
                                'value' => 'value2',
                                'operator' => 'END',
                                'not' => false,
                            ],
                        ]
                    ]
                ]
            ],
            // parameter1[][operator1][not]=value1&parameter1[][operator2]=value2&parameter2[][operator3]=value3&parameter2[][operator4]=value4&
            [
                // query
                [
                    'param1' => [['gt' => ['not' => 'value1']], ['lte' => 'value2']],
                    'order' => [['asc' => 'param1'], ['desc' => 'param5']],
                ],
                // expected parameters
                [
                    [
                        'name' => 'param1',
                        'commands' => [
                            [
                                'value' => 'value1',
                                'operator' => 'GREATER_THAN',
                                'not' => true,
                            ],
                            [
                                'value' => 'value2',
                                'operator' => 'LESS_THAN_EQUALS',
                                'not' => false,
                            ],
                        ]
                    ],
                    [
                        'name' => 'order',
                        'commands' => [
                            [
                                'value' => 'param1',
                                'operator' => 'ASCENDING',
                                'not' => false,
                            ],
                            [
                                'value' => 'param5',
                                'operator' => 'DESCENDING',
                                'not' => false,
                            ],
                        ]
                    ],
                ]
            ],
            // next scenario
        ];
    }
}