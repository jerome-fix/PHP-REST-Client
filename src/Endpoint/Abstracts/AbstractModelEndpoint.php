<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\REST\Endpoint\Interfaces\ModelInterface;

/**
 * Class AbstractModelEndpoint
 * @package MRussell\REST\Endpoint\Abstracts
 */
abstract class AbstractModelEndpoint extends AbstractEndpoint implements ModelInterface
{
    const MODEL_ID_VAR = 'id';

    const MODEL_ACTION_CREATE = 'create';

    const MODEL_ACTION_READ = 'read';

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
        'read' => Curl::HTTP_GET,
        'update' => Curl::HTTP_PUT,
        'delete' => Curl::HTTP_DELETE
    );

    /**
     * List of available actions and their associated Request Method
     * @var array
     */
    protected $actions = array();

    /**
     * Current action being executed
     * @var string
     */
    protected $action = 'read';

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

    public function __construct(array $options, array $properties) {
        parent::__construct($options, $properties);
        foreach(static::$_DEFAULT_ACTIONS as $action => $method){
            $this->actions[$action] = $method;
        }
    }

    public function __call($name, $arguments) {
        if (array_key_exists($name,$this->actions)){
            $this->action = $name;
            return $this->execute();
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key) {
        return $this->data[$key];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequestException
     */
    public function retrieve($id = NULL) {
        if ($id !== NULL){
            $idKey = static::modelIdKey();
            if (isset($this->data[$idKey])){
                $this->data->clear();
            }
            $this->data[$idKey] = $id;
        }
        $this->action = self::MODEL_ACTION_READ;
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

    /**
     * Configures Action before configuring Request
     * @inheritdoc
     */
    protected function configureRequest() {
        $this->configureAction($this->action);
        parent::configureRequest();
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
     *
     */
    protected function configureResponse() {
        parent::configureResponse();
        if ($this->Response->getStatus() == '200'){
            $this->data->update($this->Response->getBody());
        }
    }

    /**
     * @param array $options
     * @return mixed|string
     */
    protected function configureURL(array $options)
    {
        $url = parent::configureURL($options);
        $variables = $this->extractUrlVariables($url);
        if (!empty($variables)){
            $modelIdKey = static::modelIdKey();
            foreach($variables as $key => $var){
                if (strpos($var,self::MODEL_ID_VAR) !== FALSE){
                    $search = static::$_URL_VAR_CHARACTER.self::MODEL_ID_VAR;
                    $replace = '';
                    if (isset($this->data[$modelIdKey])){
                        $replace = $this->data[$modelIdKey];
                    }
                    $url = str_replace($search,$replace,$url);
                }
            }
        }
        return $url;
    }

}
