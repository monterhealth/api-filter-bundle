<?php
/**
 * Created by PhpStorm.
 * User: nelis
 * Date: 8/7/2018
 * Time: 9:32 PM
 */

namespace Monter\ApiFilterBundle\Parameter;

/**
 * Collects Parameter objects
 *
 * Class FCollection
 * @package Monter\ApiFilterBundle\Parameter
 */
class Collection implements \Iterator, \Countable
{
    private $position = 0;

    private $parameters = [];


    public function add(Parameter $parameter): self
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function getUnused(): self
    {
        $collection = new self();
        foreach($this as $parameter) {
            if(!$parameter->isUsed()) {
                $collection->add($parameter);
            }
        }

        return $collection;
    }

    public function getUnusedByName($name): ?Parameter
    {
        foreach($this->getUnused() as $parameter) {
            if($parameter->getName() === $name) {
                return $parameter;
            }
        }

        return null;
    }

    public function current(): Parameter
    {
        return $this->parameters[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->parameters[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return \count($this->parameters);
    }


}