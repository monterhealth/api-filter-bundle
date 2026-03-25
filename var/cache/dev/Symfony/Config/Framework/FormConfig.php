<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Form'.\DIRECTORY_SEPARATOR.'CsrfProtectionConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormConfig 
{
    private $enabled;
    private $csrfProtection;
    private $legacyErrorMessages;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":null,"field_name":"_token"}
     * @return \Symfony\Config\Framework\Form\CsrfProtectionConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Form\CsrfProtectionConfig : static)
     */
    public function csrfProtection(array|bool $value = []): \Symfony\Config\Framework\Form\CsrfProtectionConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = $value;

            return $this;
        }

        if (!$this->csrfProtection instanceof \Symfony\Config\Framework\Form\CsrfProtectionConfig) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = new \Symfony\Config\Framework\Form\CsrfProtectionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "csrfProtection()" has already been initialized. You cannot pass values the second time you call csrfProtection().');
        }

        return $this->csrfProtection;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @deprecated Since symfony/framework-bundle 6.2: The child node "legacy_error_messages" at path "framework.form" is deprecated.
     * @return $this
     */
    public function legacyErrorMessages($value): static
    {
        $this->_usedProperties['legacyErrorMessages'] = true;
        $this->legacyErrorMessages = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('csrf_protection', $config)) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = \is_array($config['csrf_protection']) ? new \Symfony\Config\Framework\Form\CsrfProtectionConfig($config['csrf_protection']) : $config['csrf_protection'];
            unset($config['csrf_protection']);
        }

        if (array_key_exists('legacy_error_messages', $config)) {
            $this->_usedProperties['legacyErrorMessages'] = true;
            $this->legacyErrorMessages = $config['legacy_error_messages'];
            unset($config['legacy_error_messages']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enabled'])) {
            $output['enabled'] = $this->enabled;
        }
        if (isset($this->_usedProperties['csrfProtection'])) {
            $output['csrf_protection'] = $this->csrfProtection instanceof \Symfony\Config\Framework\Form\CsrfProtectionConfig ? $this->csrfProtection->toArray() : $this->csrfProtection;
        }
        if (isset($this->_usedProperties['legacyErrorMessages'])) {
            $output['legacy_error_messages'] = $this->legacyErrorMessages;
        }

        return $output;
    }

}
