<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\REST\Endpoint\Interfaces\CollectionInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractCollectionEndpoint extends AbstractEndpoint implements CollectionInterface
{
    protected static $_ENDPOINT_URL = '';

    /**
     * @var string
     */
    protected static $_MODEL_CLASS = '';

    /**
     * The Collection of Models
     * @var array
     */
    protected $collection = array();

    /**
     * @var AbstractModelEndpoint
     */
    protected $_Model;

    public function __construct(array $options, array $properties) {
        parent::__construct($options, $properties);
        if (!empty(static::$_ENDPOINT_URL)){
            $this->setModelEndpoint(static::$_ENDPOINT_URL);
        }
    }

    public function fetch() {
        $this->setProperty('httpMethod',Curl::HTTP_GET);
        $this->execute();
    }

    protected function configureResponse(){
        parent::configureResponse();
        $this->configureCollection();
    }

    public function getCollection() {
        return $this->collection;
    }

    protected function configureCollection(){
        if ($this->Response->getStatus() == '200'){
            $responseBody = $this->Response->getBody();
            if (isset($this->_Model)){
                $modelIdKey = $this->_Model->modelIdKey();
                foreach($responseBody as $key => $model){
                    $this->collection[$model[$modelIdKey]] = $model;
                }
            } else {
                $this->collection = $responseBody;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get($id) {
        $data = $this->collection[$id];
        if (isset($this->_Model)){
            $Data = $this->_Model->getData();
            $Data->clear();
            foreach($data as $key => $value){
                $data[$key] = $value;
            }
            return $this->_Model;
        } else {
            return $data;
        }
    }

    public function setModelEndpoint($model) {
        if (!is_object($model)){
            if (class_exists($model)){
                $Model = new $model();
            } else {
                throw new UnknownEndpoint(get_called_class());
            }
        } else {
            $Model = $model;
        }
        $implements = class_implements($Model);
        if (is_array($implements) && in_array('MRussell\REST\Endpoint\Interfaces\ModelInterface',$implements)){
            $this->_Model = $Model;
        } else {
            throw new UnknownEndpoint(get_called_class());
        }
    }

    public function getEndPointUrl($full = FALSE) {
        $epURL = parent::getEndPointUrl();
        if ($epURL == '' && isset($this->_Model)){
            $epURL = $this->_Model->getEndPointUrl();
        }
        return $epURL;
    }
}