<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Auth\InvalidAuthenticationAction;
use MRussell\REST\Storage\StorageControllerInterface;

abstract class AbstractAuthController implements AuthControllerInterface {
    const ACTION_AUTH = 'authenticate';
    const ACTION_LOGOUT = 'logout';

    /**
     * Auth Controller Actions, used to associate Endpoints
     * @var array
     */
    protected static $_DEFAULT_AUTH_ACTIONS = array(
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
    protected $token = null;

    /**
     * @var StorageControllerInterface
     */
    protected $storage;

    public function __construct() {
        foreach (static::$_DEFAULT_AUTH_ACTIONS as $action) {
            $this->actions[] = $action;
        }
    }

    /**
     * @inheritdoc
     */
    public function setCredentials(array $credentials): AuthControllerInterface {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(): array {
        return $this->credentials;
    }

    /**
     * Set the Token on the Authentication Controller
     * @param $token
     * @return $this
     */
    protected function setToken($token): AuthControllerInterface {
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
     * Clear the token property to null
     */
    protected function clearToken(): AuthControllerInterface {
        $this->token = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setActions(array $actions): AuthControllerInterface {
        $this->actions = $actions;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function setActionEndpoint(string $action, EndpointInterface $Endpoint): AuthControllerInterface {
        if (in_array($action, $this->actions)) {
            $this->endpoints[$action] = $Endpoint;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActionEndpoint($action): EndpointInterface {
        if (isset($this->endpoints[$action])) {
            return $this->endpoints[$action];
        }
        throw new InvalidAuthenticationAction([$action, __CLASS__]);
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated(): bool {
        if (!empty($this->token)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(): bool {
        $Endpoint = $this->configureEndpoint($this->getActionEndpoint(self::ACTION_AUTH), self::ACTION_AUTH);
        try {
            $response = $Endpoint->execute()->getResponse();
            $ret = $response->getStatusCode() == 200;
            if ($ret) {
                //@codeCoverageIgnoreStart
                $this->setToken($response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            // TODO: Add a proper PSR-7 Logger here
            $ret = false;
        }
        
        //@codeCoverageIgnoreEnd
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function logout(): bool {
        $ret = false;
        $Endpoint = $this->getActionEndpoint(self::ACTION_LOGOUT);
        if ($Endpoint !== null) {
            $Endpoint = $this->configureEndpoint($Endpoint, self::ACTION_LOGOUT);
            $response = $Endpoint->execute()->getResponse();
            $ret = $response->getStatusCode() == 200;
            if ($ret) {
                $this->clearToken();
            }
        }
        return $ret;
    }

    /**
     * @inheritDoc
     **/
    public function reset(): AuthControllerInterface {
        $this->credentials = array();
        return $this->clearToken();
    }

    /**
     * @inheritdoc
     */
    public function setStorageController(StorageControllerInterface $Storage): AuthControllerInterface {
        $this->storage = $Storage;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStorageController(): StorageControllerInterface {
        return $this->storage;
    }

    /**
     * @inheritdoc
     */
    public function storeToken($key, $token): bool {
        if (isset($this->storage)) {
            return $this->storage->store($key, $token);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStoredToken($key) {
        if (isset($this->storage)) {
            return $this->storage->get($key);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function removeStoredToken($key): bool {
        if (isset($this->storage)) {
            return $this->storage->remove($key);
        }
        return false;
    }

    /**
     * Configure an actions Endpoint Object
     * @param EndpointInterface $Endpoint
     * @param string $action
     * @return EndpointInterface
     */
    protected function configureEndpoint(EndpointInterface $Endpoint, $action): EndpointInterface {
        switch ($action) {
            case self::ACTION_AUTH:
                $Endpoint = $this->configureAuthenticationEndpoint($Endpoint);
                break;
            case self::ACTION_LOGOUT:
                $Endpoint = $this->configureLogoutEndpoint($Endpoint);
                break;
        }
        return $Endpoint;
    }

    /**
     * Configure the data for the given Endpoint
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureAuthenticationEndpoint(EndpointInterface $Endpoint): EndpointInterface {
        return $Endpoint->setData($this->credentials);
    }

    /**
     *
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureLogoutEndpoint(EndpointInterface $Endpoint): EndpointInterface {
        return $Endpoint->setData(array());
    }
}
