<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/19/2018
 * Time: 8:14 PM
 */

namespace Monter\ApiFilterBundle\Tests\Parameter;


use Monter\ApiFilterBundle\Parameter\Collection;
use Monter\ApiFilterBundle\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testGetUnused(): void
    {
        $collection = new Collection();

        $parameters = [];

        $this->assertEquals(new Collection(), $collection->getUnused());

        foreach($this->getParameters() as $parameter) {

            if(!$parameter->isUsed()) {
                $parameters[] = $parameter;
            }

            $collection->add($parameter);

            $unusedCollection = $collection->getUnused();

            $this->assertInstanceOf(Collection::class, $unusedCollection);

            $unusedArray = [];

            foreach($unusedCollection as $unusedParameter) {
                $unusedArray[] = $unusedParameter;
            }

            $this->assertEquals($parameters, $unusedArray);
        }
    }

    public function testGetUnusedByName(): void
    {
        $collection = new Collection();

        $this->assertNull($collection->getUnusedByName('test'));

        /** @var Parameter $parameter */
        foreach($this->getParameters() as $parameter) {

            $this->assertNull($collection->getUnusedByName($parameter->getName()));

            $collection->add($parameter);

            if($parameter->isUsed()) {
                $this->assertNull($collection->getUnusedByName($parameter->getName()));
            } else {
                $this->assertSame($parameter, $collection->getUnusedByName($parameter->getName()));
            }
        }
    }

    private function getParameters(): array
    {
        $parameters = [
            new Parameter('1'),
            new Parameter('2'),
            new Parameter('3'),
            new Parameter('4'),
            new Parameter('five'),
        ];

        $parameters[1]->setUsed(true);
        $parameters[4]->setUsed(true);

        return $parameters;
    }
}