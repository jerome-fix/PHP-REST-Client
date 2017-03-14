<?php
/**
 * ©[2016] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace MRussell\REST\Client;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\ControllerInterface;
use MRussell\REST\Endpoint\Provider\EndpointProviderInterface;
use MRussell\REST\Exception\Authentication\AuthenticationException;
use MRussell\REST\Response\Provider\ResponseProviderInterface;
use MRussell\REST\Response\ResponseControllerInterface;
use MRussell\REST\Storage\StorageControllerInterface;

interface ClientInterface
{

    /**
     *
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
     *
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
     *
     * @return string $version
     */
    public function getVersion();

    /**
     * @return ControllerInterface
     */
    public function current();

    /**
     * @return ControllerInterface
     */
    public function last();

    /**
     * @return mixed
     */
    public function error();


}
