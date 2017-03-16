<?php

namespace MRussell\REST\Endpoint\Provider;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\ControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Endpoint\RegistrationException;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractEndpointProvider implements EndpointProviderInterface
{

    /**
     * List of default endpoints to load
     * @var array
     */
    protected static $_DEFAULT_ENDPOINTS = array();

    /**
     * @var array
     */
    protected $registry;

    public function __construct() {
        foreach(static::$_DEFAULT_ENDPOINTS as $name => $epData){
            if (!isset($epData['properties'])){
                $epData['properties'] = array();
            }
            $this->registerEndpoint($name,$epData['class'],$epData['properties']);
        }
    }

    /**
     * @inheritdoc
     * @throws RegistrationException
     */
    public function registerEndpoint($name, $className, array $properties = array())
    {
        $implements = class_implements($className);
        if (is_array($implements) && in_array('MRussell\REST\Endpoint\EndpointInterface', $implements)) {
            $this->registry[$name] = array(
                'class' => $className,
                'properties' => $properties
            );
        } else {
            throw new RegistrationException($className);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasEndpoint($name, $version = NULL) {
        return array_key_exists($name,$this->registry);
    }

    /**
     * @inheritdoc
     * @throws UnknownEndpoint
     */
    public function getEndpoint($name, $version = NULL) {
        if ($this->hasEndpoint($name,$version)){
            return $this->buildEndpoint($name,$version);
        }else{
            throw new UnknownEndpoint($name);
        }
    }

    /**
     * @param $name
     * @param null $version
     * @return EndpointInterface
     */
    protected function buildEndpoint($name,$version = NULL){
        $endPointArray = $this->registry[$name];
        $Class = $endPointArray['class'];
        $properties = $endPointArray['properties'];
        $Endpoint = new $Class();
        $Endpoint->setProperties($properties);
        return $Endpoint;
    }
}