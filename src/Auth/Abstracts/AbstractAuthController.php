<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Data\EndpointData;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Storage\StorageControllerInterface;

abstract class AbstractAuthController implements AuthControllerInterface
{
    const ACTION_AUTH = 'authenticate';

    const ACTION_LOGOUT = 'logout';

    /**
     * Auth Controller Actions
     * @var array
     */
    private static $_DEFAULT_AUTH_ACTIONS = array(
        self::ACTION_AUTH,
        self::ACTION_LOGOUT,
    );

    /**
     * Configured Actions on the Controlller
     * @var array
     */
    protected $actions = array();

    /**
     * Configured Endpoints for configured actions
     * @var array
     */
    protected $endpoints = array();

    /**
     * The credentials used for authentication
     * @var array
     */
    protected $credentials = array();

    /**
     * The authentication token
     * @var mixed
     */
    protected $token = NULL;

    /**
     * @var StorageControllerInterface
     */
    protected $Storage;

    public function __construct() {
        foreach(self::$_DEFAULT_AUTH_ACTIONS as $action){
            $this->actions[] = $action;
        }
    }

    /**
     * @inheritdoc
     */
    public function setCredentials(array $credentials) {
        $this->credentials = $credentials;
    }

    /**
     * @inheritdoc
     */
    public function getCredentials() {
        return $this->credentials;
    }

    /**
     * Set the Token on the Authentication Controller
     * @param $token
     * @return $this
     */
    protected function setToken($token){
        $this->token = $token;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Clear the token property to NULL
     */
    protected function clearToken(){
        $this->token = NULL;
    }

    public function setActions(array $actions) {
        $this->actions = $actions;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActions() {
        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function setActionEndpoint($action, EndpointInterface $Endpoint) {
        if (in_array($action,$this->actions)){
            $this->endpoints[$action] = $Endpoint;
        }
        return $this;
    }

    public function getActionEndpoint($action) {
        if (isset($this->endpoints[$action])){
            return $this->endpoints[$action];
        }
        return NULL;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated() {
        if (isset($this->token)){
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function authenticate() {
        $Endpoint = $this->configureData(self::ACTION_AUTH);
        $response = $Endpoint->execute()->getResponse();
        if ($response->getStatus() == '200'){
            $this->setToken($response->getBody(true));
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function logout(){
        $Endpoint = $this->configureData(self::ACTION_LOGOUT);
        $response = $Endpoint->execute()->getResponse();
        if ($response->getStatus() == '200'){
            $this->clearToken();
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setStorageController(StorageControllerInterface $Storage) {
        $this->Storage = $Storage;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStorageController() {
        return $this->Storage;
    }

    /**
     * @inheritdoc
     */
    public function storeToken($key, $token) {
        if ($this->getStorageController()->set($key,$token)){
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStoredToken($key) {
        return $this->getStorageController()->get($key);
    }

    /**
     *
     * @param $action
     * @return bool|EndpointInterface
     */
    protected function configureData($action){
        $EP = $this->getActionEndpoint($action);
        if ($EP !== NULL){
            switch($action){
                case self::ACTION_AUTH:
                    return $this->configureAuthenticationData($EP);
                case self::ACTION_LOGOUT:
                    return $this->configureLogoutData($EP);
            }
        }
        return FALSE;
    }

    /**
     * Configure the data for the given Endpoint
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureAuthenticationData(EndpointInterface $Endpoint){
        $Data = $Endpoint->getData();
        if (empty($Data)||!is_object($Data)){
            $Data = new EndpointData();
        }
        foreach($this->credentials as $key => $value){
            $Data[$key] = $value;
        }
        return $Endpoint->setData($Data);
    }

    /**
     *
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureLogoutData(EndpointInterface $Endpoint){
        $Data = $Endpoint->getData();
        if (is_object($Data)){
            $Data->clear();
        }
        return $Endpoint->setData($Data);
    }

}