<?php

namespace MRussell\REST\Endpoint\Abstracts;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\CollectionInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractCollectionEndpoint extends AbstractSmartEndpoint implements CollectionInterface, DataInterface {
    const EVENT_BEFORE_SYNC = 'before_sync';

    /**
     * @var string
     */
    protected static $_MODEL_CLASS = '';

    /**
     * @var string
     */
    protected static $_RESPONSE_PROP = '';

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
        if (static::$_MODEL_CLASS !== '') {
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
    public function offsetSet($offset, $value): void {
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
    public function offsetExists($offset): bool {
        return isset($this->collection[$offset]);
    }

    /**
     * Unsets an offset
     * @param string $offset - The offset to unset
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset): void {
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
    public function toArray(): array {
        return $this->collection;
    }

    /**
     * @return self
     */
    public function reset(): DataInterface {
        return $this->clear();
    }

    /**
     *
     * @return self
     */
    public function clear(): DataInterface {
        $this->collection = array();
        return $this;
    }

    /**
     * Update and append to Collection array
     * @param array $collection
     * @return self
     */
    public function update(array $collection): DataInterface {
        foreach ($collection as $key => $value) {
            $this->collection[$key] = $value;
        }
        return $this;
    }

    //Collection Interface
    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequest
     */
    public function fetch(): self {
        $this->setProperty(self::PROPERTY_HTTP_METHOD, "GET");
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function get($id) {
        $data = null;
        if ($this->offsetExists($id)) {
            $data = $this->collection[$id];
            $Model = $this->buildModel($data);
            if ($Model !== null) {
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
    public function at($index) {
        $return = null;
        $index = intval($index);
        $data = $this->toArray();
        reset($data);
        if ($index < 0) {
            $index += $this->length();
        }
        $c = 1;
        while ($c <= $index) {
            next($data);
            $c++;
        }
        $return = current($data);
        $Model = $this->buildModel($return);
        if ($Model !== null) {
            $return = $Model;
        }
        return $return;
    }

    /**
     * Return the current collection count
     * @return int
     */
    public function length(): int {
        return count($this->collection);
    }

    /**
     * @inheritdoc
     * @throws UnknownEndpoint
     */
    public function setModelEndpoint($model): CollectionInterface {
        try {
            $implements = class_implements($model);
            if (is_array($implements) && isset($implements['MRussell\REST\Endpoint\Interfaces\ModelInterface'])) {
                if (is_object($model)) {
                    $model = get_class($model);
                }
                $this->model = $model;
                return $this;
            }
        } catch (\Exception $ex) {
            //If class_implements cannot load class
        }
        throw new UnknownEndpoint($model);
    }

    /**
     * @param bool $full
     * @return string
     */
    public function getEndPointUrl($full = false): string {
        $epURL = parent::getEndPointUrl();
        if ($epURL == '' && isset($this->model)) {
            $epURL = $this->buildModel()->getEndPointUrl();
        }
        if ($full) {
            $epURL = rtrim($this->getBaseUrl(), "/") . "/$epURL";
        }
        return $epURL;
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
     * @inheritdoc
     */
    protected function parseResponse(Response $response): void {
        if ($response->getStatusCode() == 200) {
            $body = $this->getResponseBody();
            $this->syncFromApi($this->parseCollectionFromResponseBody($body));
        }
    }

    /**
     * @param object|array $body
     * @return array
     */
    protected function parseCollectionFromResponseBody($body): array {
        $prop = static::$_RESPONSE_PROP;
        $ret = [];

        if (!$prop) {
            $ret = is_array($body) ? $body : [];
        } else {
            if (is_object($body)) {
                $ret = $body->$prop ?? [];
            } else {
                $ret = $body[$prop] ?? [];
            }
        }
        return $ret;
    }

    /**
     * Configures the collection based on the Response Body
     */
    protected function syncFromApi(array $data) {
        $this->triggerEvent(self::EVENT_BEFORE_SYNC, $data);
        if (isset($this->model)) {
            $modelIdKey = $this->buildModel()->modelIdKey();
            foreach ($data as $key => $model) {
                if (isset($model[$modelIdKey])) {
                    $this->collection[$model[$modelIdKey]] = $model;
                } else {
                    $this->collection[] = $model;
                }
            }
        } else {
            $this->collection = $data;
        }
    }

    /**
     * Build the ModelEndpoint
     * @param array $data
     * @return AbstractModelEndpoint
     */
    protected function buildModel(array $data = array()): AbstractModelEndpoint {
        $Model = null;
        if (isset($this->model)) {
            $Model = new $this->model();
            $Model->setBaseUrl($this->getBaseUrl());
            $Model->setHttpClient($this->getHttpClient());
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $Model->set($key, $value);
                }
            }
        }
        return $Model;
    }
}
