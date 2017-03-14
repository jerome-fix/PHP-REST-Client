<?php

namespace MRussell\REST\Endpoint\Provider;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\ControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Endpoint\RegistrationException;
use MRussell\REST\Exception\Endpoint\UnknownEndpoint;

abstract class AbstractEndpointProvider implements EndpointProviderInterface {

    /**
     * @var AuthControllerInterface
     */
    protected $Auth;

    /**
     * @var array
     */
    protected $registry;

    /**
     *
     * @inheritdoc
     */
    public function setAuth(AuthControllerInterface $Auth)
    {
        $this->Auth = $Auth;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAuth()
    {
        return $this->Auth;
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
        $Endpoint = new $endPointArray['class']();
        if ($Endpoint->authRequired()){
            $this->Auth->configure($Endpoint);
        }
        return $Endpoint;
    }

    /**
     * @param $name
     */
    protected function authRequired($name){

    }
}