<?php

namespace MRussell\REST\Auth;

use MRussell\REST\Endpoint\Data\EndpointData;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Storage\StorageControllerInterface;

abstract class AbstractAuthController implements AuthControllerInterface
{
    /**
     * @var string
     */
    protected static $_AUTH_ENDPOINT_CLASS = '';

    /**
     * @var string
     */
    protected static $_LOGOUT_ENDPOINT_CLASS = '';

    /**
     * @var StorageControllerInterface
     */
    protected $Storage;

    /**
     * @var array
     */
    protected $credentials = array();

    /**
     * @var mixed
     */
    protected $token = NULL;

    /**
     * @var EndpointInterface
     */
    protected $AuthEndpoint;

    /**
     * @var EndpointInterface
     */
    protected $LogoutEndpoint;

    public function __construct() {
        if (static::$_AUTH_ENDPOINT_CLASS !== ''){
            $this->setAuthenticationEndpoint(new static::$_AUTH_ENDPOINT_CLASS());
        }
        if (static::$_LOGOUT_ENDPOINT_CLASS !== ''){
            $this->setLogoutEndpoint(new static::$_AUTH_ENDPOINT_CLASS());
        }
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
     * @inheritdoc
     */
    public function getToken() {
        return $this->token;
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
    public function isAuthenticated() {
        if (isset($this->token)){
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setAuthenticationEndpoint(EndpointInterface $Endpoint) {
        $this->AuthEndpoint = $Endpoint;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLogoutEndpoint(EndpointInterface $Endpoint) {
        $this->LogoutEndpoint = $Endpoint;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authenticate() {
        $Data = $this->AuthEndpoint->getData();
        if (empty($Data)){
            $Data = new EndpointData();
        }
        $Data->update($this->credentials);
        $response = $this->AuthEndpoint->setData($Data)->execute()->getRequest()->getResponse();
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
        $this->configure($this->LogoutEndpoint);
        $response = $this->LogoutEndpoint->execute()->getRequest()->getResponse();
        if ($response->getStatus() == '200'){
            $this->clearToken();
            return true;
        }
        return false;
    }

    protected function clearToken(){
        $this->token = NULL;
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
}