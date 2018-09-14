<?php

namespace MonterHealth\ApiFilterBundle\Filter;


class FilterResult
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $result;
    /**
     * @var array
     */
    private $queryParameters = [];
    /**
     * @var array
     */
    private $joins = [];
    /**
     * @var array
     */
    private $settings;

    public function __construct(string $type, string $result, array $queryParameters = [], array $joins = [], array $settings = [])
    {
        $this->type = $type;
        $this->result = $result;
        $this->queryParameters = $queryParameters;
        $this->joins = $joins;
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    /**
     * @return bool
     */
    public function hasQueryParameters(): bool
    {
        return \count($this->queryParameters) > 0;
    }

    /**
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return bool
     */
    public function hasJoins(): bool
    {
        return \count($this->joins) > 0;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getSetting($name)
    {
        return $this->settings[$name] ?? null;
    }
}