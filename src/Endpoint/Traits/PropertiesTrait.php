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
     * @param array $properties
     * @return $this
     * @implements PropertiesInterface
     */
    public function setProperties(array $properties) {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     * @implements PropertiesInterface
     */
    public function setProperty(string $name, $value) {
        $this->properties[$name] = $value;
        return $this;
    }
}