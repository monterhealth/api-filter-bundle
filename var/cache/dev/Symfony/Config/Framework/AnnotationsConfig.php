<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AnnotationsConfig 
{
    private $enabled;
    private $cache;
    private $fileCacheDir;
    private $debug;
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
     * @default 'php_array'
     * @param ParamConfigurator|'none'|'php_array'|'file' $value
     * @return $this
     */
    public function cache($value): static
    {
        $this->_usedProperties['cache'] = true;
        $this->cache = $value;

        return $this;
    }

    /**
     * @default '%kernel.cache_dir%/annotations'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function fileCacheDir($value): static
    {
        $this->_usedProperties['fileCacheDir'] = true;
        $this->fileCacheDir = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function debug($value): static
    {
        $this->_usedProperties['debug'] = true;
        $this->debug = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = $config['cache'];
            unset($config['cache']);
        }

        if (array_key_exists('file_cache_dir', $config)) {
            $this->_usedProperties['fileCacheDir'] = true;
            $this->fileCacheDir = $config['file_cache_dir'];
            unset($config['file_cache_dir']);
        }

        if (array_key_exists('debug', $config)) {
            $this->_usedProperties['debug'] = true;
            $this->debug = $config['debug'];
            unset($config['debug']);
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
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache;
        }
        if (isset($this->_usedProperties['fileCacheDir'])) {
            $output['file_cache_dir'] = $this->fileCacheDir;
        }
        if (isset($this->_usedProperties['debug'])) {
            $output['debug'] = $this->debug;
        }

        return $output;
    }

}
