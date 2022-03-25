<?php

namespace MRussell\REST\Auth;

use GuzzleHttp\Psr7\Request;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Storage\StorageControllerInterface;

interface AuthControllerInterface
{
    /**
     * Get the configured Array of credentials used for authentication
     * @return array
     */
    public function getCredentials(): array;

    /**
     * Set the credentials used for authentication
     * @param array $credentials
     * @return $this
     */
    public function setCredentials(array $credentials);

    /**
     * Update parts of credentials used for authentication
     * @param array $credentials
     * @return $this
     */
    public function updateCredentials(array $credentials);

    /**
     * @param array $actions
     * @return $this
     */
    public function setActions(array $actions);

    /**
     * @return array
     */
    public function getActions(): array;

    /**
     * @param string $action
     * @param EndpointInterface $Endpoint
     * @return $this
     */
    public function setActionEndpoint(string $action, EndpointInterface $Endpoint);

    /**
     * Get the Endpoint configured for an action
     * @param string $action
     * @return EndpointInterface
     */
    public function getActionEndpoint(string $action): EndpointInterface;

    /**
     * Configure a provided Request with proper Authentication/Authorization
     * Used by Client HttpClient Handler Middleware
     * @param Request $Request
     * @return $this
     */
    public function configureRequest(Request $Request): Request;

    /**
     * Execute the authentication scheme
     * @return boolean
     */
    public function authenticate(): bool;

    /**
     * Do necessary actions to Logout
     * @return boolean
     */
    public function logout(): bool;

    /**
     * Reset the auth controller to default state. Does not call 'logout' but does clear current token/credentials
     * @return $this
     */
    public function reset();

    /**
     * Is currently authenticated
     * @return boolean
     */
    public function isAuthenticated(): bool;

    /**
     * Set the storage Controller
     * @param StorageControllerInterface $Storage
     * @return $this
     */
    public function setStorageController(StorageControllerInterface $Storage);

    /**
     * Get the Storage Controller used by the Auth Controller
     * @return StorageControllerInterface
     */
    public function getStorageController(): StorageControllerInterface;

    /**
     * Get the current token on the Auth Controller
     * @return mixed
     */
    public function getToken();

    /**
     * Store a token so it can be used in other PHP threads
     * @param mixed $key
     * @param mixed $token
     * @return boolean
     */
    public function storeToken($key, $token): bool;

    /**
     * Retrieve a Token from local storage
     * @param $key
     * @return mixed
     */
    public function getStoredToken($key);

    /**
     * Remove a token from storage
     * @param mixed $key
     * @return bool
     */
    public function removeStoredToken($key): bool;
}
