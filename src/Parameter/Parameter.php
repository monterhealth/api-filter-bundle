<?php

namespace MonterHealth\ApiFilterBundle\Parameter;

/**
 * Stores all information about one filter parameter from the Request query
 *
 * Class Parameter
 * @package MonterHealth\ApiFilterBundle\Parameter
 */
class Parameter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var bool
     */
    private $used = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addCommand(Command $command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getFirstCommand(): ?Command
    {
        return $this->hasCommands() ? $this->commands[0] : null;
    }

    public function hasCommands(): bool
    {
        return (bool) \count($this->commands);
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }
}