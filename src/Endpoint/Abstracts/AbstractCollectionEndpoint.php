<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\Http\Response\ResponseInterface;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\CollectionInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractCollectionEndpoint extends AbstractSmartEndpoint implements CollectionInterface, DataInterface
{
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
     * The Class Name of the ModelEndpoint
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
        if ($this->offsetExists($id)){
            $data = $this->collection[$id];
            $Model = $this->buildModel($data);
            if ($Model !== NULL){
                $data = $Model;
            }
        }
        return $data;
    }

    /**
     * Get a model based on numerical index
     * @param int $index
     * @return array|ModelInterface
     */
    public function at($index){
        $return = NULL;
        $index = intval($index);
        $data = $this->asArray();
        reset($data);
        if ($index < 0){
            $index += $this->length();
        }
        $c = 1;
        while ($c <= $index){
            next($data);
            $c++;
        }
        $return = current($data);
        $Model = $this->buildModel($return);
        if ($Model !== NULL){
            $return = $Model;
        }
        return $return;
    }

    /**
     * Return the current collection count
     * @return int
     */
    public function length(){
        return count($this->collection);
    }

    /**
     * @inheritdoc
     * @throws UnknownEndpoint
     */
    public function setModelEndpoint($model) {
        try{
            $implements = class_implements($model);
            if (is_array($implements) && isset($implements['MRussell\REST\Endpoint\Interfaces\ModelInterface'])){
                if (is_object($model)){
                    $model = get_class($model);
                }
                $this->model = $model;
                return $this;
            }
        } catch (\Exception $ex){
            //If class_implements cannot load class
        }
        throw new UnknownEndpoint($model);
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
        if ($full){
            $epURL = rtrim($this->getBaseUrl(),"/")."/$epURL";
        }
        return $epURL;
    }

    /**
     * @inheritdoc
     */
    protected function configureResponse(ResponseInterface $Response) {
        $Response = parent::configureResponse($Response);
        if ($Response->getStatus() == '200'){
            //@codeCoverageIgnoreStart
            $this->updateCollection();
        }
        //@codeCoverageIgnoreEnd
        return $Response;
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
     * Build the ModelEndpoint
     * @param array $data
     * @return AbstractModelEndpoint
     */
    protected function buildModel(array $data = array()){
        $Model = NULL;
        if (isset($this->model)){
            $Model = new $this->model();
            $Model->setBaseUrl($this->getBaseUrl());
            if ($this->getAuth() !== NULL) {
                $Model->setAuth($this->getAuth());
            }
            if (!empty($data)){
                foreach($data as $key => $value){
                    $Model->set($key,$value);
                }
            }
        }
        return $Model;
    }
}