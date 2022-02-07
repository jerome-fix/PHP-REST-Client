<?php

namespace MRussell\REST\Endpoint\Data;

use MRussell\REST\Endpoint\Interfaces\ResettableInterface;
use MRussell\REST\Endpoint\Traits\ArrayObjectAttributesTrait;
use MRussell\REST\Endpoint\Traits\ClearAttributesTrait;
use MRussell\REST\Endpoint\Traits\GetAttributesTrait;
use MRussell\REST\Endpoint\Traits\PropertiesTrait;
use MRussell\REST\Endpoint\Traits\SetAttributesTrait;
use MRussell\REST\Exception\Endpoint\InvalidData;

abstract class AbstractEndpointData implements DataInterface {
    use SetAttributesTrait,
        GetAttributesTrait,
        ClearAttributesTrait;
    use PropertiesTrait {
        setProperties as rawSetProperties;
    }
    use ArrayObjectAttributesTrait {
        toArray as asArray;
    }


    const DATA_PROPERTY_REQUIRED = 'required';
    const DATA_PROPERTY_DEFAULTS = 'defaults';

    protected static $_DEFAULT_PROPERTIES = array(
        self::DATA_PROPERTY_REQUIRED => [],
        self::DATA_PROPERTY_DEFAULTS => [],
    );

    //Overloads
    public function __construct(array $data = [],array $properties = []) {
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        foreach ($properties as $key => $value) {
            $this->properties[$key] = $value;
        }
        $this->configureDefaultData();
        $this->set($data);
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
        $this->clear();
        return $this->configureDefaultData();
    }

    /**
     * Configures Data with defaults based on properties array
     * @return $this
     */
    protected function configureDefaultData(): self {
        if (isset($this->properties['defaults']) && is_array($this->properties['defaults'])) {
            foreach ($this->properties['defaults'] as $data => $value) {
                if (!isset($this->attributes[$data])) {
                    $this->attributes[$data] = $value;
                }
            }
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
        return $this->asArray();
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
        if (!empty($this->properties['required'])) {
            foreach ($this->properties['required'] as $property => $type) {
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
                $errorMsg .= "Missing [" . implode(",", $errors['missing']) . "]";
            }
            if (!empty($errors['invalid'])) {
                if (!empty($errors['missing'])){
                    $errorMsg .= " ";
                }
                $errorMsg .= "Invalid [" . implode(",", $errors['invalid']) . "]";
            }
            throw new InvalidData($errorMsg);
        }
        return $error;
    }
}
