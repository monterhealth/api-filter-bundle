<?php

namespace Symfony\Config\Framework\Mailer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class EnvelopeConfig 
{
    private $sender;
    private $recipients;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function sender($value): static
    {
        $this->_usedProperties['sender'] = true;
        $this->sender = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function recipients(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['recipients'] = true;
        $this->recipients = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('sender', $config)) {
            $this->_usedProperties['sender'] = true;
            $this->sender = $config['sender'];
            unset($config['sender']);
        }

        if (array_key_exists('recipients', $config)) {
            $this->_usedProperties['recipients'] = true;
            $this->recipients = $config['recipients'];
            unset($config['recipients']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['sender'])) {
            $output['sender'] = $this->sender;
        }
        if (isset($this->_usedProperties['recipients'])) {
            $output['recipients'] = $this->recipients;
        }

        return $output;
    }

}
