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
     * Check if Endpoint is registered
     * @param $name
     * @param null $version
     * @return boolean
     */
    public function hasEndpoint($name,$version = NULL);

}