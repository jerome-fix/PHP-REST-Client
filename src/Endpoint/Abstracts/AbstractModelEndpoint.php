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

    /**
     * The ID Field used by the Model
     * @var string
     */
    protected static $_MODEL_ID_KEY = 'id';

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
        $this->setProperty('httpMethod',Curl::HTTP_GET);
        $this->execute();
    }


    /**
     * @inheritdoc
     * @throws \MRussell\REST\Exception\Endpoint\InvalidRequestException
     */
    public function save() {
        if (isset($this->data[static::modelIdKey()])){
            $this->setProperty('httpMethod',Curl::HTTP_PUT);
        } else {
            $this->setProperty('httpMethod',Curl::HTTP_POST);
        }
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function delete(){
        $this->setProperty('httpMethod',Curl::HTTP_DELETE);
        $this->execute();
    }

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
