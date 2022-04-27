<?php

namespace MRussell\REST\Endpoint\Traits;

use MRussell\REST\Endpoint\Interfaces\PropertiesInterface;

trait PropertiesTrait
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Get the current Data Properties
     * @return array
     * @implements PropertiesInterface
     */
    public function getProperties(): array {
        return $this->properties;
    }

    /**
     * Set the properties array
     * @param array $properties
     * @return $this
     * @implements PropertiesInterface
     */
    public function setProperties(array $properties) {
        $this->properties = $properties;
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
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Get a specific property from properties array
     * @param string $name
     * @return mixed
     * @implements PropertiesInterface
     */
    public function getProperty(string $name) {
        if (isset($this->properties[$name])){
            return $this->properties[$name];
        }
        return null;
    }
}