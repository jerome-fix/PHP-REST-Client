<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\Http\Response\ResponseInterface;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\CollectionInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractCollectionEndpoint extends AbstractEndpoint implements CollectionInterface, DataInterface
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
     * The Class Name
     * @var string
     */
    protected $model;

    public function __construct(array $options = array(), array $properties = array()) {
        parent::__construct($options, $properties);
        if (static::$_MODEL_CLASS !== ''){
            $this->setModelEndpoint(static::$_MODEL_CLASS);
        }
    }

    //Data Interface
    /**
     * Assigns a value to the specified offset
     * @param string $offset - The offset to assign the value to
     * @param mixed $value - The value to set
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     * @param string $offset - An offset to check for
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->collection[$offset]);
    }

    /**
     * Unsets an offset
     * @param string $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->collection[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     * @param string $offset - The offset to retrieve
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->collection[$offset] : null;
    }

    /**
     * @return array
     */
    public function asArray(){
        return $this->collection;
    }

    /**
     * @return self
     */
    public function reset(){
        return $this->clear();
    }

    /**
     *
     * @return self
     */
    public function clear(){
        $this->collection = array();
        return $this;
    }

    /**
     * Update and append to Collection array
     * @param array $collection
     * @return self
     */
    public function update(array $collection){
        foreach($collection as $key => $value){
            $this->collection[$key] = $value;
        }
        return $this;
    }

    //Collection Interface
    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function fetch() {
        $this->setProperty('httpMethod',Curl::HTTP_GET);
        $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function get($id) {
        $data = NULL;
        if (isset($this->collection[$id])){
            $data = $this->collection[$id];
            if (isset($this->_Model) && is_array($data)){
                $Model = $this->buildModel();
                foreach($data as $key => $value){
                   $Model->set($key,$value);
                }
                $data = $Model;
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     * @throws UnknownEndpoint
     */
    public function setModelEndpoint($model) {
        $implements = class_implements($model);
        if (is_array($implements) && in_array('MRussell\REST\Endpoint\Interfaces\ModelInterface',$implements)){
            $this->model = $model;
        } else {
            throw new UnknownEndpoint($model);
        }
    }

    /**
     * @param bool $full
     * @return string
     */
    public function getEndPointUrl($full = FALSE) {
        $epURL = parent::getEndPointUrl();
        if ($epURL == '' && isset($this->model)){
            $epURL = $this->buildModel()->getEndPointUrl();
        }
        return $epURL;
    }

    /**
     * @inheritdoc
     */
    protected function configureResponse(ResponseInterface $Response) {
        $Response = parent::configureResponse($Response);
        if ($Response->getStatus() == '200'){
            $this->updateCollection();
        }
    }

    /**
     * Configures the collection based on the Response Body
     */
    protected function updateCollection(){
        $responseBody = $this->Response->getBody();
        if (is_array($responseBody)){
            if (isset($this->model)){
                $modelIdKey = $this->buildModel()->modelIdKey();
                foreach($responseBody as $key => $model){
                    if (isset($model[$modelIdKey])){
                        $this->collection[$model[$modelIdKey]] = $model;
                    } else {
                        $this->collection[] = $model;
                    }
                }
            } else {
                $this->collection = $responseBody;
            }
        }
    }

    /**
     * @return ModelInterface
     */
    protected function buildModel(){
        if (isset($this->model)){
            return new $this->model();
        }
        return NULL;
    }
}