<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\Http\Response\ResponseInterface;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Endpoint\JSON\ModelEndpoint;
use MRussell\REST\Exception\Endpoint\EndpointException;
use MRussell\REST\Exception\Endpoint\MissingModelId;
use MRussell\REST\Exception\Endpoint\UnknownModelAction;

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
    public function __construct(array $options = array(), array $properties = array()) {
        parent::__construct($options, $properties);
        foreach(static::$_DEFAULT_ACTIONS as $action => $method){
            $this->actions[$action] = $method;
        }
    }

    public function __call($name, $arguments) {
        if (array_key_exists($name,$this->actions)){
            $this->action = $name;
            $this->configureAction($this->action,$arguments);
            return $this->execute();
        }
        throw new UnknownModelAction(array(get_class($this),$name));
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
            $this->model[] = $value;
        } else {
            $this->model[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     * @param string $offset - An offset to check for
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->model[$offset]);
    }

    /**
     * Unsets an offset
     * @param string $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->model[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     * @param string $offset - The offset to retrieve
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->model[$offset] : null;
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
        return $this->offsetGet($key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
        $this->offsetSet($key,$value);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function retrieve($id = NULL) {
        $this->action = self::MODEL_ACTION_RETRIEVE;
        $idKey = $this->modelIdKey();
        if ($id !== NULL){
            if (isset($this->model[$idKey])){
                $this->reset();
            }
            $this->set($idKey,$id);
        } else {
            if (!isset($this->model[$idKey])){
                throw new MissingModelId(array($this->action,get_class($this)));
            }
        }
        $this->configureAction($this->action);
        return $this->execute();
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function save() {
        if (isset($this->model[$this->modelIdKey()])){
            $this->action = self::MODEL_ACTION_UPDATE;
        } else {
            $this->action = self::MODEL_ACTION_CREATE;
        }
        $this->configureAction($this->action);
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function delete(){
        $this->action = self::MODEL_ACTION_DELETE;
        $this->configureAction($this->action);
        return $this->execute();
    }

    //Endpoint Overrides
    /**
     * Update any properties or data based on the current action
     * - Called before Execute on dynamic
     * @param $action
     * @param array $arguments
     */
    protected function configureAction($action,array $arguments = array()){
        if (isset($this->actions[$action])){
            $this->setProperty('httpMethod',$this->actions[$action]);
        }
    }

    /**
     * @param AbstractEndpointData $data
     * @inheritdoc
     */
    protected function configureData($data)
    {
        $requestData = parent::configureData($data);
        if ($requestData == NULL){
            $requestData = array();
        }
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
        $idKey = $this->modelIdKey();
        $id = $this->get($idKey);
        $options[self::MODEL_ID_VAR] = (empty($id)?'':$id);
        return parent::configureURL($options);
    }

}
