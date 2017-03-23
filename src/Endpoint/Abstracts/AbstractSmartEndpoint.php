<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Exception\Endpoint\EndpointException;

abstract class AbstractSmartEndpoint extends AbstractEndpoint
{
    const PROPERTY_DATA = 'data';

    /**
     * @inheritdoc
     */
    protected static $_DEFAULT_PROPERTIES = array(
        'url' => '',
        'httpMethod' => '',
        'auth' => FALSE,
        'data' => array(
            'required' => array(),
            'defaults' => array()
        )
    );

    protected static $_DATA_CLASS = '';

    /**
     * The data being passed to the API Endpoint.
     * Defaults to Array, but can be mixed based on how you want to use Endpoint.
     * @var AbstractEndpointData
     */
    protected $data;

    public function __construct(array $options = array(), array $properties = array()) {
        parent::__construct($options, $properties);
        if (static::$_DATA_CLASS !== '' && !empty(static::$_DATA_CLASS)){
            $implements = class_implements(static::$_DATA_CLASS);
            if (is_array($implements) && isset($implements["MRussell\\REST\\Endpoint\\Data\\DataInterface"])){
                $data = new static::$_DATA_CLASS($this->properties['data']);
                $this->setData($data);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function setProperties(array $properties) {
        if (!isset($properties[self::PROPERTY_DATA])){
            $properties[self::PROPERTY_DATA] = array(
                'required' => array(),
                'defaults' => array()
            );
        }
        parent::setProperties($properties);
        if (isset($this->data)){
            $this->configureDataProperties();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProperty($name, $value) {
        parent::setProperty($name, $value);
        if ($name == self::PROPERTY_DATA && isset($this->data)){
            $this->configureDataProperties();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setData($data) {
        if ($data instanceof AbstractEndpointData){
            $this->data = $data;
        } else if (is_array($data) && is_object($this->data)){
            $this->data->reset();
            $this->data->update($data);
        } else {
            throw new EndpointException("Invalid data passed to Endpoint");
        }
        return $this;
    }

    /**
     * Passes Data properties to Endpoint Data object
     * @return $this
     */
    protected function configureDataProperties(){
        if (isset($this->properties['data'])){
            $this->data->setProperties($this->properties['data']);
        }
        return $this;
    }

    /**
     * @param AbstractEndpointData $data
     * @inheritdoc
     */
    protected function configureData($data) {
        return parent::configureData($data->asArray());
    }
}