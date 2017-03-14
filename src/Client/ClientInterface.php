<?php

namespace MRussell\REST\Client;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Endpoint\Provider\EndpointProviderInterface;

interface ClientInterface
{

    /**
     * Set the Auth Controller that handles Auth for the API
     * @param AuthControllerInterface $Auth
     * @return $this
     */
    public function setAuth(AuthControllerInterface $Auth);

    /**
     *
     * @return AuthControllerInterface
     */
    public function getAuth();

    /**
     * Set the Endpoint Provider that is to be used by the REST Client
     * @param EndpointProviderInterface $EndpointProvider
     * @return $this
     */
    public function setEndpointProvider(EndpointProviderInterface $EndpointProvider);

    /**
     *
     * @return EndpointProviderInterface
     */
    public function getEndpointProvider();

    /**
     * Set the server on the Client, and configure the API Url if necessary
     * @param $server
     * @return $this
     */
    public function setServer($server);

    /**
     * Get the server configured on SDK Client
     * @return mixed
     */
    public function getServer();

    /**
     * Get the configured API Url on the SDK Client
     * @return string
     */
    public function getAPIUrl();

    /**
     * Set the API Version to use
     * @param $version
     * @return $this
     */
    public function setVersion($version);

    /**
     * Set the Client API Version that is to be used
     * @return string $version
     */
    public function getVersion();

    /**
     * Get the Endpoint currently being used
     * @return EndpointInterface
     */
    public function current();

    /**
     * Get the last Endpoint Used
     * @return EndpointInterface
     */
    public function last();

    /**
     * @return mixed
     */
    public function error();


}
