<?php

namespace MRussell\REST\Endpoint\Data;

use MRussell\REST\Endpoint\Interfaces\ArrayableInterface;
use MRussell\REST\Endpoint\Interfaces\ResettableInterface;
use MRussell\REST\Endpoint\Traits\ArrayObjectAttributesTrait;
use MRussell\REST\Endpoint\Traits\ClearAttributesTrait;
use MRussell\REST\Endpoint\Traits\GetAttributesTrait;
use MRussell\REST\Endpoint\Traits\PropertiesTrait;
use MRussell\REST\Endpoint\Traits\SetAttributesTrait;
use MRussell\REST\Exception\Endpoint\InvalidData;

abstract class AbstractEndpointData implements DataInterface {
    use GetAttributesTrait,
        ClearAttributesTrait;
    use SetAttributesTrait {
        set as private setAttributes;
    }
    use PropertiesTrait {
        setProperties as rawSetProperties;
    }

    /**
     * A way to determine between Empty Array and Null
     * @var bool
     */
    protected $isNull = true;

    /**
     * The actual data
     * @var array
     */
    protected $attributes = [];

    const DATA_PROPERTY_REQUIRED = 'required';
    const DATA_PROPERTY_DEFAULTS = 'defaults';

    protected static $_DEFAULT_PROPERTIES = array(
        self::DATA_PROPERTY_REQUIRED => [],
        self::DATA_PROPERTY_DEFAULTS => [],
    );

    //Overloads
    public function __construct(array $data = null,array $properties = []) {
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        if (!empty($properties)){
            foreach ($properties as $key => $value) {
                $this->setProperty($key,$value);
            }
        }
        $this->configureDefaultData();
        if (!empty($data)){
            $this->set($data);
        }
    }

    //Object Access
    /**
     * Get a data by key
     * @param string The key data to retrieve
     * @access public
     */
    public function &__get($key) {
        return $this->attributes[$key];
    }

    /**
     * Assigns a value to the specified data
     * @param string $key - The data key to assign the value to
     * @param mixed $value - The value to set
     */
    public function __set($key, $value) {
        $this->isNull = false;
        $this->attributes[$key] = $value;
    }

    /**
     * Whether or not an data exists by key
     * @param string $key - A data key to check for
     * @return boolean
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unsets data by key
     * @param string $key - The key to unset
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

    //Array Access
    /**
     * Assigns a value to the specified offset
     * @param string $offset - The offset to assign the value to
     * @param mixed $value - The value to set
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset, $value): void {
        $this->isNull = false;
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     * @param string $offset - An offset to check for
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset): bool {
        return isset($this->attributes[$offset]);
    }

    /**
     * Unsets an offset
     * @param string $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset): void {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     * @param string $offset - The offset to retrieve
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->attributes[$offset] : null;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key,$value = null)
    {
        if ((is_array($key) && !empty($key)) || !is_array($key)){
            $this->isNull = false;
        }
        return $this->setAttributes($key,$value);
    }

    /**
     * Set properties for data
     * @param array $properties
     */
    public function setProperties(array $properties): void
    {
        if (!isset($properties[self::DATA_PROPERTY_REQUIRED])) {
            $properties[self::DATA_PROPERTY_REQUIRED] = [];
        }
        if (!isset($properties[self::DATA_PROPERTY_DEFAULTS])) {
            $properties[self::DATA_PROPERTY_DEFAULTS] = [];
        }
        $this->rawSetProperties($properties);
    }

    /**
     * Set Data back to Defaults and clear out data
     * @return AbstractEndpointData
     * @implements ResettableInterface
     */
    public function reset(): DataInterface {
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        $this->null();
        return $this->configureDefaultData();
    }

    /**
     * Set data to null
     * @return $this
     */
    public function null()
    {
        $this->clear();
        $this->isNull = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNull(): bool {
        return $this->isNull && empty($this->attributes);
    }

    /**
     * Configures Data with defaults based on properties array
     * @return $this
     */
    protected function configureDefaultData(): self {
        if (isset($this->properties[self::DATA_PROPERTY_DEFAULTS])
            && is_array($this->properties[self::DATA_PROPERTY_DEFAULTS])
            && !empty($this->properties[self::DATA_PROPERTY_DEFAULTS])) {
            $this->set($this->properties[self::DATA_PROPERTY_DEFAULTS]);
        }
        return $this;
    }

    /**
     * Verify data requirements when converting to Array
     * @param bool $verify
     * @return array
     * @throws InvalidData
     */
    public function toArray(bool $verify = false): array
    {
        if ($verify){
            $this->verifyRequiredData();
        }
        return $this->attributes;
    }

    /**
     * Validate Required Data for the Endpoint
     * @return bool
     * @throws InvalidData
     */
    protected function verifyRequiredData(): bool {
        $errors = [
            'missing' => [],
            'invalid' => []
        ];
        $error = false;
        if (!empty($this->properties[self::DATA_PROPERTY_REQUIRED])) {
            foreach ($this->properties[self::DATA_PROPERTY_REQUIRED] as $property => $type) {
                if (!isset($this->attributes[$property])) {
                    $errors['missing'][] = $property;
                    $error = true;
                    continue;
                }
                if ($type !== null && gettype($this->attributes[$property]) !== $type) {
                    $errors['invalid'][] = $property;
                    $error = true;
                }
            }
        }
        if ($error) {
            $errorMsg = '';
            if (!empty($errors['missing'])) {
                $errorMsg .= "Missing [" . implode(",", $errors['missing']) . "] ";
            }
            if (!empty($errors['invalid'])) {
                $errorMsg .= "Invalid [" . implode(",", $errors['invalid']) . "]";
            }
            throw new InvalidData(trim($errorMsg));
        }
        return $error;
    }
}
