<?php

namespace MRussell\REST\Endpoint\Data;

use MRussell\REST\Exception\Endpoint\RequiredDataException;

abstract class AbstractEndpointData implements DataInterface
{
    protected static $_DEFAULT_PROPERTIES = array(
        'required' => array(),
        'defaults' => array(),
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
    public function __construct(array $data = array(),array $properties = array()) {
        $this->reset();
        $this->data = $data;
        foreach($properties as $key => $value){
            $this->properties[$key] = $value;
        }
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
     * @throws RequiredDataException
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
        $this->properties = $properties;
        return $this;
    }

    /**
     * Set Data back to Defaults and clear out data
     * @return AbstractEndpointData
     */
    public function reset(){
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        $this->configureDefaultData();
        return $this->clear();
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
     * @throws RequiredDataException
     */
    protected function verifyRequiredData()
    {
        $error = FALSE;
        if (!empty($this->properties['required'])) {
            foreach ($this->properties['required'] as $property => $type) {
                if (!isset($data[$property])) {
                    $error = TRUE;
                }
                if ($type !== NULL && gettype($data[$property]) !== $type) {
                    $error = TRUE;
                }
            }
        }
        if ($error){
            throw new RequiredDataException(get_called_class());
        }
        return $error;
    }

}