<?php

namespace Symfony\Config;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MonterHealthApiFilterConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $orderParameterName;
    private $defaultOrderStrategy;
    private $parameterCollectionFactory;
    private $attributeReader;
    private $apiFilterFactory;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * The name of the query parameter to order results.
     * @default 'order'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function orderParameterName($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['orderParameterName'] = true;
        $this->orderParameterName = $value;

        return $this;
    }

    /**
     * The default order strategy (ascending or descending).
     * @default 'ascending'
     * @param ParamConfigurator|'ascending'|'descending' $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function defaultOrderStrategy($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['defaultOrderStrategy'] = true;
        $this->defaultOrderStrategy = $value;

        return $this;
    }

    /**
     * Possibility to override the default ParameterCollectionFactory.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function parameterCollectionFactory($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['parameterCollectionFactory'] = true;
        $this->parameterCollectionFactory = $value;

        return $this;
    }

    /**
     * Possibility to override the default attribute reader.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function attributeReader($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['attributeReader'] = true;
        $this->attributeReader = $value;

        return $this;
    }

    /**
     * Possibility to override the default ApiFilterFactory.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function apiFilterFactory($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['apiFilterFactory'] = true;
        $this->apiFilterFactory = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'monter_health_api_filter';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('order_parameter_name', $config)) {
            $this->_usedProperties['orderParameterName'] = true;
            $this->orderParameterName = $config['order_parameter_name'];
            unset($config['order_parameter_name']);
        }

        if (array_key_exists('default_order_strategy', $config)) {
            $this->_usedProperties['defaultOrderStrategy'] = true;
            $this->defaultOrderStrategy = $config['default_order_strategy'];
            unset($config['default_order_strategy']);
        }

        if (array_key_exists('parameter_collection_factory', $config)) {
            $this->_usedProperties['parameterCollectionFactory'] = true;
            $this->parameterCollectionFactory = $config['parameter_collection_factory'];
            unset($config['parameter_collection_factory']);
        }

        if (array_key_exists('attribute_reader', $config)) {
            $this->_usedProperties['attributeReader'] = true;
            $this->attributeReader = $config['attribute_reader'];
            unset($config['attribute_reader']);
        }

        if (array_key_exists('api_filter_factory', $config)) {
            $this->_usedProperties['apiFilterFactory'] = true;
            $this->apiFilterFactory = $config['api_filter_factory'];
            unset($config['api_filter_factory']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['orderParameterName'])) {
            $output['order_parameter_name'] = $this->orderParameterName;
        }
        if (isset($this->_usedProperties['defaultOrderStrategy'])) {
            $output['default_order_strategy'] = $this->defaultOrderStrategy;
        }
        if (isset($this->_usedProperties['parameterCollectionFactory'])) {
            $output['parameter_collection_factory'] = $this->parameterCollectionFactory;
        }
        if (isset($this->_usedProperties['attributeReader'])) {
            $output['attribute_reader'] = $this->attributeReader;
        }
        if (isset($this->_usedProperties['apiFilterFactory'])) {
            $output['api_filter_factory'] = $this->apiFilterFactory;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
