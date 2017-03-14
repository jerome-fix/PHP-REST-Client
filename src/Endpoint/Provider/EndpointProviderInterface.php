<?php

namespace MRussell\REST\Endpoint\Provider;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;

interface EndpointProviderInterface {

    /**
     * @param string $name
     * @param string $version
     * @return EndpointInterface
     */
    public function getEndpoint($name,$version = NULL);

    /**
     *
     * @param string $name
     * @param string $className
     * @param array $properties
     * @return $this
     */
    public function registerEndpoint($name,$className,array $properties = array());


    /**
     * Set the Auth controller used to add Authentication to Endpoint objects
     * @param AuthControllerInterface $Auth
     * @return $this
     */
    public function setAuth(AuthControllerInterface $Auth);

    /**
     * @return mixed
     */
    public function getAuth();

    /**
     * Check if Endpoint is registered
     * @param $name
     * @param null $version
     * @return boolean
     */
    public function hasEndpoint($name,$version = NULL);

}