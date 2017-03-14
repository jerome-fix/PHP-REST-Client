<?php

namespace MRussell\REST\Auth;

use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Storage\StorageControllerInterface;

interface AuthControllerInterface
{

    /**
     * @param StorageControllerInterface $Storage
     * @return $this
     */
    public function setStorageController(StorageControllerInterface $Storage);

    /**
     *
     * @return StorageControllerInterface
     */
    public function getStorageController();

    /**
     *
     * @return array
     */
    public function getCredentials();

    /**
     *
     * @param array $credentials
     * @return $this
     */
    public function setCredentials(array $credentials);

    /**
     * @return mixed
     */
    public function getToken();

    /**
     * @param $key
     * @param $token
     * @return boolean
     */
    public function storeToken($key,$token);

    /**
     * @param $key
     * @return mixed
     */
    public function getStoredToken($key);


    /**
     * @param EndpointInterface $Endpoint
     * @return $this
     */
    public function setAuthenticationEndpoint(EndpointInterface $Endpoint);

    /**
     *
     * @param EndpointInterface $Endpoint
     * @return $this
     */
    public function setLogoutEndpoint(EndpointInterface $Endpoint);

    /**
     * Configure a provided Endpoint with proper Authentication/Authorization
     * @param EndpointInterface $Endpoint
     * @return $this
     */
    public function configure(EndpointInterface $Endpoint);

    /**
     * Do the configured authentication
     * @return boolean
     */
    public function authenticate();

    /**
     *
     * @return boolean
     */
    public function logout();

    /**
     * Is currently authenticated
     * @return boolean
     */
    public function isAuthenticated();


}