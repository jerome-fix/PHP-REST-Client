<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Response\ResponseInterface;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Data\EndpointData;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\Exception;

/**
 * Class AbstractModelEndpoint
 * @package MRussell\REST\Endpoint\Abstracts
 */
abstract class AbstractModelEndpoint extends AbstractSmartEndpoint implements ModelInterface, DataInterface
{
    const MODEL_ID_VAR = 'id';

    const MODEL_ACTION_CREATE = 'create';

    const MODEL_ACTION_RETRIEVE = 'retrieve';

    const MODEL_ACTION_UPDATE = 'update';

    const MODEL_ACTION_DELETE = 'delete';

    /**
     * The ID Field used by the Model
     * @var string
     */
    protected static $_MODEL_ID_KEY = 'id';

    /**
     * List of actions
     * @var array
     */
    protected static $_DEFAULT_ACTIONS = array(
        'create' => Curl::HTTP_POST,
        'retrieve' => Curl::HTTP_GET,
        'update' => Curl::HTTP_PUT,
        'delete' => Curl::HTTP_DELETE
    );

    /**
     * The Model
     * @var array
     */
    protected $model = array();

    /**
     * List of available actions and their associated Request Method
     * @var array
     */
    protected $actions = array();

    /**
     * Current action being executed
     * @var string
     */
    protected $action = 'retrieve';

    //Static
    /**
     * @param null $id
     * @return string
     */
    public static function modelIdKey($id = NULL) {
        if ($id !== NULL){
            static::$_MODEL_ID_KEY = $id;
        }
        return static::$_MODEL_ID_KEY;
    }

    //Overloads
    public function __construct(array $options, array $properties) {
        parent::__construct($options, $properties);
        foreach(static::$_DEFAULT_ACTIONS as $action => $method){
            $this->actions[$action] = $method;
        }
    }

    public function __call($name, $arguments) {
        if (array_key_exists($name,$this->actions)){
            $this->action = $name;
            return $this->execute($arguments);
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

    /**
     * @inheritdoc
     */
    public function asArray(){
        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function reset(){
        return $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function clear(){
        $this->model = array();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function update(array $model){
        foreach($model as $key => $value){
            $this->model[$key] = $value;
        }
        return $this;
    }

    //Model Interface
    /**
     * @inheritdoc
     */
    public function get($key) {
        return $this->model[$key];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
        $this->model[$key] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequestException
     */
    public function retrieve($id = NULL) {
        $idKey = static::modelIdKey();
        if ($id !== NULL){
            if (isset($this->model[$idKey])){
                $this->reset();
                $this->set($idKey,$id);
            }
        } else {
            if (!isset($this->model[$idKey])){
                throw new Exception("Cannot retrieve Model without an ID");
            }
        }
        $this->action = self::MODEL_ACTION_RETRIEVE;
        $this->execute();
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequestException
     */
    public function save() {
        if (isset($this->data[static::modelIdKey()])){
            $this->action = self::MODEL_ACTION_UPDATE;
        } else {
            $this->action = self::MODEL_ACTION_CREATE;
        }
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function delete(){
        $this->action = self::MODEL_ACTION_DELETE;
        $this->execute();
    }

    //Endpoint Overrides
    /**
     * Configures Action before configuring Request
     * @inheritdoc
     */
    protected function configureRequest(RequestInterface $Request) {
        $this->configureAction($this->action);
        parent::configureRequest($Request);
    }

    /**
     * Update any properties or data based on the current action
     * @param $action
     */
    protected function configureAction($action){
        if (isset($this->actions[$action])){
            $this->setProperty('httpMethod',$this->actions[$action]);
        }
    }

    /**
     * @param AbstractEndpointData $data
     * @inheritdoc
     */
    protected function configureData(AbstractEndpointData $data) {
        $requestData = $data->asArray(TRUE);
        switch ($this->action){
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_UPDATE:
                $requestData = array_replace($requestData,$this->asArray());
                break;
        }
        return $requestData;
    }

    /**
     * @inheritdoc
     */
    protected function configureResponse(ResponseInterface $Response) {
        $Response = parent::configureResponse($Response);
        if ($Response->getStatus() == '200'){
            $this->updateModel();
        }
        return $Response;
    }

    /**
     * Called after Execute if a Request Object exists, and Request returned 200 response
     */
    protected function updateModel(){
        $body = $this->Response->getBody();
        switch ($this->action){
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_UPDATE:
            case self::MODEL_ACTION_RETRIEVE:
                if (is_array($body)){
                    $this->update($body);
                }
                break;
            case self::MODEL_ACTION_DELETE:
                $this->clear();
        }
    }

    /**
     * @param array $options
     * @return mixed|string
     */
    protected function configureURL(array $options)
    {
        $id = '';
        $modelIdKey = static::modelIdKey();
        if (isset($this->model[$modelIdKey])){
            $id = $this->model[$modelIdKey];
        }
        $options[self::MODEL_ID_VAR] = $id;
        return parent::configureURL($options);
    }

}
