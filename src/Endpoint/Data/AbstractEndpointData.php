<?php

namespace MRussell\REST\Endpoint\Data;

use MRussell\REST\Exception\Endpoint\InvalidData;

abstract class AbstractEndpointData implements DataInterface
{
    const DATA_PROPERTY_REQUIRED = 'required';

    const DATA_PROPERTY_DEFAULTS = 'defaults';

    protected static $_DEFAULT_PROPERTIES = array(
        self::DATA_PROPERTY_REQUIRED => array(),
        self::DATA_PROPERTY_DEFAULTS => array(),
    );

    /**
     * The array representation of the Data
     * @var array
     */
    private $data = array();

    /**
     * The properties Array that provide useful attributes to internal logic of Data
     * @var array
     */
    protected $properties;

    //Overloads
    public function __construct(array $properties = array(),array $data = array()) {
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        foreach($properties as $key => $value){
            $this->properties[$key] = $value;
        }
        $this->configureDefaultData();
        $this->update($data);
    }

    /**
     * Get a data by key
     * @param string The key data to retrieve
     * @access public
     */
    public function &__get ($key) {
        return $this->data[$key];
    }

    /**
     * Assigns a value to the specified data
     * @param string $key - The data key to assign the value to
     * @param mixed $value - The value to set
     */
    public function __set($key,$value) {
        $this->data[$key] = $value;
    }

    /**
     * Whether or not an data exists by key
     * @param string $key - A data key to check for
     * @return boolean
     */
    public function __isset($key) {
        return isset($this->data[$key]);
    }

    /**
     * Unsets data by key
     * @param string $key - The key to unset
     */
    public function __unset($key) {
        unset($this->data[$key]);
    }

    //Array Access
    /**
     * Assigns a value to the specified offset
     * @param string $offset - The offset to assign the value to
     * @param mixed $value - The value to set
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     * @param string $offset - An offset to check for
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets an offset
     * @param string $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     * @param string $offset - The offset to retrieve
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    //Data Interface
    /**
     * Return the entire Data array
     * @param bool $verify - Whether or not to verify if Required Data is filled in
     * @return array
     * @throws InvalidData
     */
    public function asArray($verify = FALSE){
        if ($verify){
            $this->verifyRequiredData();
        }
        return $this->data;
    }

    /**
     * Get the current Data Properties
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * Set properties for data
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties) {
        if (!isset($properties[self::DATA_PROPERTY_REQUIRED])){
            $properties[self::DATA_PROPERTY_REQUIRED] = array();
        }
        if (!isset($properties[self::DATA_PROPERTY_DEFAULTS])){
            $properties[self::DATA_PROPERTY_DEFAULTS] = array();
        }
        $this->properties = $properties;
        return $this;
    }

    /**
     * Set Data back to Defaults and clear out data
     * @return AbstractEndpointData
     */
    public function reset(){
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        $this->clear();
        return $this->configureDefaultData();
    }

    /**
     * Clear out data array
     * @return $this
     */
    public function clear(){
        $this->data = array();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function update(array $data){
        foreach($data as $key => $value){
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Configures Data with defaults based on properties array
     * @return $this
     */
    protected function configureDefaultData(){
        if (isset($this->properties['defaults']) && is_array($this->properties['defaults'])){
            foreach($this->properties['defaults'] as $data => $value){
                if (!isset($this->data[$data])){
                    $this->data[$data] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Validate Required Data for the Endpoint
     * @return bool
     * @throws InvalidData
     */
    protected function verifyRequiredData()
    {
        $errors = array(
            'missing' => array(),
            'invalid' => array()
        );
        $error = FALSE;
        if (!empty($this->properties['required'])) {
            foreach ($this->properties['required'] as $property => $type) {
                if (!isset($this->data[$property])) {
                    $errors['missing'][] = $property;
                    $error = TRUE;
                    continue;
                }
                if ($type !== NULL && gettype($this->data[$property]) !== $type) {
                    $errors['invalid'][] = $property;
                    $error = TRUE;
                }
            }
        }
        if ($error){
            $errorMsg = '';
            if (!empty($errors['missing'])){
                $errorMsg .= "Missing [".implode(",",$errors['missing']). "] ";
            }
            if (!empty($errors['invalid'])){
                $errorMsg .= "Invalid [".implode(",",$errors['invalid'])."]";
            }
            throw new InvalidData($errorMsg);
        }
        return $error;
    }

}