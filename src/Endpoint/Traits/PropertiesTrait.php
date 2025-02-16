<?php

namespace MRussell\REST\Endpoint\Traits;

use MRussell\REST\Endpoint\Interfaces\PropertiesInterface;

trait PropertiesTrait
{
    /**
     * @var array
     */
    protected $_properties = [];

    /**
     * Get the current Data Properties
     * @return array
     * @implements PropertiesInterface
     */
    public function getProperties(): array {
        return $this->_properties;
    }

    /**
     * Set the properties array
     * @param array $properties
     * @return $this
     * @implements PropertiesInterface
     */
    public function setProperties(array $properties) {
        $this->_properties = $properties;
        return $this;
    }

    /**
     * Set a property in properties array
     * @param string $name
     * @param $value
     * @return $this
     * @implements PropertiesInterface
     */
    public function setProperty(string $name, $value) {
        $properties = $this->getProperties();
        $properties[$name] = $value;
        return $this->setProperties($properties);
    }

    /**
     * Get a specific property from properties array
     * @param string $name
     * @return mixed
     * @implements PropertiesInterface
     */
    public function getProperty(string $name) {
        if (isset($this->_properties[$name])){
            return $this->_properties[$name];
        }
        return null;
    }
}