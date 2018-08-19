<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/19/2018
 * Time: 7:52 PM
 */

namespace Monter\ApiFilterBundle\Tests\Parameter;


use Monter\ApiFilterBundle\Parameter\Command;
use Monter\ApiFilterBundle\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testName(): void
    {
        $name = 'test';

        $parameter = new Parameter($name);

        $this->assertSame($name, $parameter->getName());
    }

    public function testGetCommands(): void
    {
        $parameter = new Parameter('test');

        $commands = [];

        $this->assertSame($commands, $parameter->getCommands());

        foreach($this->getCommands() as $command) {
            $commands[] = $command;

            $parameter->addCommand($command);

            $this->assertSame($commands, $parameter->getCommands());
        }
    }


    public function testHasCommands(): void
    {
        $parameter = new Parameter('test');

        $this->assertFalse($parameter->hasCommands());

        foreach($this->getCommands() as $command) {
            $parameter->addCommand($command);

            $this->assertTrue($parameter->hasCommands());
        }
    }

    public function testFirstCommand(): void
    {
        $parameter = new Parameter('test');

        $this->assertNull($parameter->getFirstCommand());

        $firstCommand = $this->getCommands()[0];

        foreach($this->getCommands() as $command) {
            $parameter->addCommand($command);

            $this->assertEquals($firstCommand, $parameter->getFirstCommand());
        }
    }

    public function testIsUsed(): void
    {
        $parameter = new Parameter('test');

        $this->assertFalse($parameter->isUsed());

        $parameter->setUsed(true);

        $this->assertTrue($parameter->isUsed());

        $parameter->setUsed(false);

        $this->assertFalse($parameter->isUsed());
    }

    private function getCommands(): array
    {
        return [
            new Command(1),
            new Command(2),
            new Command(3),
            new Command(4),
            new Command(5),
        ];
    }
}