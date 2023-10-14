<?php

namespace MonterHealth\ApiFilterBundle\Parameter\Factory;

use MonterHealth\ApiFilterBundle\Parameter\Collection;
use MonterHealth\ApiFilterBundle\Parameter\Command;
use MonterHealth\ApiFilterBundle\Parameter\Parameter;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Creates a Collection from a Http Request
 *
 * Class RequestParameterFactory
 * @package MonterHealth\ApiFilterBundle\Parameter
 */
class DefaultParameterCollectionFactory implements ParameterCollectionFactory
{
    private Collection $collection;

    /**
     * @param ParameterBag $parameterBag
     * @return Collection
     */
    public function create(ParameterBag $parameterBag): Collection
    {
// TODO: add caching
        $this->collection = new Collection();

        $this->createParameters($parameterBag);

        return $this->collection;
    }

    /**
     * Create a parameter object for each distinct query parameter
     * @param ParameterBag $parameterBag
     */
    private function createParameters(ParameterBag $parameterBag): void
    {
        foreach($parameterBag as $name => $commands) {

            // replace : with . for nested parameters
            $name = str_replace(':', '.', $name);

            $parameter = new Parameter($name);

            // set key=value to key[]=value
            if(!\is_array($commands)) {
                $commands = [$commands];
            }

            // set key[operator]=value to key[][operator] = value
            if(\is_string(\key($commands))) {
                $commands = [$commands];
            }

            foreach($commands as $command) {

                $command = $this->createCommand($command);

                if($command) {
                    // replace : with . for value
                    $command->setValue(str_replace(':', '.', $command->getValue()));

                    $parameter->addCommand($command);
                }
            }
            if($parameter->hasCommands()) {
                $this->collection->add($parameter);
            }
        }
    }

    /**
     * Processes a command like: ..[not][partial]=abie
     * @param $command
     * @return Command|null
     */
    private function createCommand($command): ?Command
    {
        $operators = [];

        // operators first
        while(\is_array($command)) {
            $operators[] = \key($command);
            $command = \current($command);
        }

        // last one must be the value
        $value = $command;

        // only create commands when there's a value found
        if(null === $value) {
            return null;
        }

        $command = new Command($value);

        foreach($operators as $operator) {
            $command->setOperator($operator);
        }

        return $command;
    }
}