<?php

namespace MRussell\REST\Endpoint\Abstracts;

use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\MissingModelId;
use MRussell\REST\Exception\Endpoint\UnknownModelAction;

/**
 * Class AbstractModelEndpoint
 * @package MRussell\REST\Endpoint\Abstracts
 */
abstract class AbstractModelEndpoint extends AbstractSmartEndpoint implements ModelInterface, DataInterface {
    const MODEL_ID_VAR = 'id';

    const MODEL_ACTION_CREATE = 'create';
    const MODEL_ACTION_RETRIEVE = 'retrieve';
    const MODEL_ACTION_UPDATE = 'update';
    const MODEL_ACTION_DELETE = 'delete';

    const EVENT_BEFORE_SAVE = 'before_save';
    const EVENT_AFTER_SAVE = 'after_save';

    const EVENT_BEFORE_DELETE = 'before_delete';
    const EVENT_AFTER_DELETE = 'after_delete';

    const EVENT_BEFORE_RETRIEVE = 'before_retrieve';
    const EVENT_AFTER_RETRIEVE = 'after_retrieve';

    const EVENT_BEFORE_SYNC = 'before_sync';

    /**
     * The ID Field used by the Model
     * @var string
     */
    protected static $_MODEL_ID_KEY = 'id';

    /**
     * The response property where the model data is located
     * @var string
     */
    protected static $_RESPONSE_PROP = '';

    /**
     * List of actions
     * @var array
     */
    protected static $_DEFAULT_ACTIONS = array(
        self::MODEL_ACTION_CREATE => 'POST',
        self::MODEL_ACTION_RETRIEVE => 'GET',
        self::MODEL_ACTION_UPDATE => 'PUT',
        self::MODEL_ACTION_DELETE => 'DELETE'
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
    protected $action = self::MODEL_ACTION_RETRIEVE;

    //Static
    /**
     * @param null $id
     * @return string
     */
    public static function modelIdKey($id = null): string {
        if ($id !== null) {
            static::$_MODEL_ID_KEY = $id;
        }
        return static::$_MODEL_ID_KEY;
    }

    //Overloads
    public function __construct(array $options = array(), array $properties = array()) {
        parent::__construct($options, $properties);
        foreach (static::$_DEFAULT_ACTIONS as $action => $method) {
            $this->actions[$action] = $method;
        }
    }

    public function __call($name, $arguments) {
        if (array_key_exists($name, $this->actions)) {
            return $this->setCurrentAction($name, $arguments)->execute();
        }
        throw new UnknownModelAction(array(get_class($this), $name));
    }

    //Data Interface
    /**
     * Assigns a value to the specified offset
     * @param string $offset - The offset to assign the value to
     * @param mixed $value - The value to set
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->model[] = $value;
        } else {
            $this->model[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     * @param string $offset - An offset to check for
     * @return bool
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset): bool {
        return isset($this->model[$offset]);
    }

    /**
     * Unsets an offset
     * @param string|int $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset): void {
        if ($this->offsetExists($offset)) {
            unset($this->model[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     * @param string|int $offset - The offset to retrieve
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->model[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array {
        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function reset(): DataInterface {
        return $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function clear(): DataInterface {
        $this->model = array();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function update(array $data): DataInterface {
        foreach ($data as $key => $value) {
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
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function retrieve($id = null): ModelInterface {
        $this->setCurrentAction(self::MODEL_ACTION_RETRIEVE);
        $idKey = $this->modelIdKey();
        if ($id !== null) {
            if (isset($this->model[$idKey])) {
                $this->reset();
            }
            $this->set($idKey, $id);
        } else if (!isset($this->model[$idKey])) {
            throw new MissingModelId(array($this->action, get_class($this)));
        }
        $this->triggerEvent(self::EVENT_BEFORE_RETRIEVE);
        $this->execute();
        $this->triggerEvent(self::EVENT_AFTER_RETRIEVE);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function save(): ModelInterface {
        if (isset($this->model[$this->modelIdKey()])) {
            $this->setCurrentAction(self::MODEL_ACTION_UPDATE);
        } else {
            $this->setCurrentAction(self::MODEL_ACTION_CREATE);
        }
        $this->triggerEvent(self::EVENT_BEFORE_SAVE);
        $this->execute();
        $this->triggerEvent(self::EVENT_AFTER_SAVE);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function delete() {
        $this->setCurrentAction(self::MODEL_ACTION_DELETE);
        $this->triggerEvent(self::EVENT_BEFORE_DELETE);
        $this->execute();
        $this->triggerEvent(self::EVENT_AFTER_DELETE);
        return $this;
    }

    /**
     * Set the current action taking place on the Model
     * @param string $action
     * @param array $actionArgs
     * @return $this
     */
    public function setCurrentAction(string $action, array $actionArgs = array()): AbstractModelEndpoint {
        if (array_key_exists($action, $this->actions)) {
            $this->action = $action;
            $this->configureAction($this->action, $actionArgs);
        }
        return $this;
    }

    /**
     * Get the current action taking place on the Model
     */
    public function getCurrentAction(): string {
        return $this->action;
    }

    /**
     * Update any properties or data based on the current action
     * - Called when setting the Current Action
     * @param $action
     * @param array $arguments
     */
    protected function configureAction($action, array $arguments = array()) {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, $this->actions[$action]);
    }

    /**
     * @param AbstractEndpointData $data
     * @inheritdoc
     */
    protected function configurePayload() {
        $requestData = parent::configurePayload();
        if ($requestData == null) {
            $requestData = $this->buildDataObject();
        }
        switch ($this->action) {
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_UPDATE:
                if (is_object($requestData)) {
                    $requestData->update($this->toArray());
                } else {
                    $requestData = array_replace($requestData->toArray(), $this->toArray());
                }
                break;
        }
        return $requestData;
    }

    /**
     * @param Response $response
     * @return EndpointInterface
     */
    public function setResponse(Response $response): EndpointInterface {
        parent::setResponse($response);
        $this->parseResponse($response);
        return $this;
    }

    /**
     * Parse the response for use by Model
     * @param Response $response
     * @return void
     */
    protected function parseResponse(Response $response): void {
        if ($response->getStatusCode() == 200) {
            switch ($this->action) {
                case self::MODEL_ACTION_CREATE:
                case self::MODEL_ACTION_UPDATE:
                case self::MODEL_ACTION_RETRIEVE:
                    $body = $this->getResponseBody();
                    $this->syncFromApi($this->parseModelFromResponseBody($body));
                    break;
                case self::MODEL_ACTION_DELETE:
                    $this->clear();
                    break;
            }
        }
    }

    /**
     * @param $body
     * @return array
     */
    protected function parseModelFromResponseBody($body): array {
        $prop = static::$_RESPONSE_PROP;
        if ($prop == '') {
            return is_array($body) ? $body : [];
        } else {
            if (is_object($body)) {
                return $body->$prop ?? [];
            } else {
                return $body[$prop] ?? [];
            }
        }
    }

    /**
     * Called after Execute if a Request Object exists, and Request returned 200 response
     */
    protected function syncFromApi(array $model) {
        $this->triggerEvent(self::EVENT_BEFORE_SYNC, $model);
        $this->update($model);
    }

    /**
     * @param array $options
     * @return string
     */
    protected function configureURL(array $options): string {
        switch ($this->getCurrentAction()) {
            case self::MODEL_ACTION_CREATE:
                $options[self::MODEL_ID_VAR] = '';
                break;
            default:
                $idKey = $this->modelIdKey();
                $id = $this->get($idKey);
                $options[self::MODEL_ID_VAR] = (empty($id) ? '' : $id);
        }
        return parent::configureURL($options);
    }
}
