<?php

namespace MonterHealth\ApiFilterBundle\Parameter;

/**
 * Collects Parameter objects
 *
 * Class FCollection
 * @package MonterHealth\ApiFilterBundle\Parameter
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
            if(\strtolower($parameter->getName()) === \strtolower($name)) {
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