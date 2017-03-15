<?php

namespace MRussell\REST\Auth;

use MRussell\Http\Request\RequestInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Storage\StorageControllerInterface;

interface AuthControllerInterface
{
    /**
     * Get the configured Array of credentials used for authentication
     * @return array
     */
    public function getCredentials();

    /**
     * Set the credentials used for authentication
     * @param array $credentials
     * @return self
     */
    public function setCredentials(array $credentials);

    /**
     * @param array $actions
     * @return mixed
     */
    public function setActions(array $actions);

    /**
     * @return array
     */
    public function getActions();

    /**
     * @param string $action
     * @param EndpointInterface $Endpoint
     * @return mixed
     */
    public function setActionEndpoint($action,EndpointInterface $Endpoint);

    /**
     * Get the Endpoint configured for an action
     * @param string $action
     * @return mixed
     */
    public function getActionEndpoint($action);

    /**
     * Configure a provided Request with proper Authentication/Authorization
     * @param RequestInterface $Request
     * @return self
     */
    public function configure(RequestInterface $Request);

    /**
     * Execute the authentication scheme
     * @return boolean
     */
    public function authenticate();

    /**
     * Do necessary actions to Logout
     * @return boolean
     */
    public function logout();

    /**
     * Is currently authenticated
     * @return boolean
     */
    public function isAuthenticated();

    /**
     * @param StorageControllerInterface $Storage
     * @return self
     */
    public function setStorageController(StorageControllerInterface $Storage);

    /**
     *
     * @return StorageControllerInterface
     */
    public function getStorageController();

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
}