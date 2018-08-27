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
    private $settings;

    public function __construct(string $type, string $result, array $settings = [])
    {
        $this->type = $type;
        $this->result = $result;
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