<?php

namespace MRussell\REST\Client;

use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Endpoint\Provider\EndpointProviderInterface;
use MRussell\REST\Exception\Client\EndpointProviderMissing;
use MRussell\REST\Exception\Endpoint\EndpointException;
use MRussell\REST\Response\Provider\ResponseProviderInterface;
use MRussell\REST\Storage\StorageControllerInterface;

/**
 * A Generic Abstract Client
 * @package MRussell\REST\Client\Abstracts\AbstractClient
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var AuthControllerInterface
     */
    protected $Auth;

    /**
     * @var EndpointProviderInterface
     */
    protected $EndpointProvider;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $apiURL;

    /**
     * @var string
     */
    protected $version = NULL;

    /**
     * @var EndpointInterface
     */
    protected $currentEndPoint;

    /**
     * @var EndpointInterface
     */
    protected $lastEndPoint;

    /**
     * @var mixed
     */
    protected $error;

    /**
     * @inheritdoc
     */
    public function setAuth(AuthControllerInterface $Auth)
    {
        $this->Auth = $Auth;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAuth()
    {
        return $this->Auth;
    }

    /**
     * @inheritdoc
     */
    public function setEndpointProvider(EndpointProviderInterface $EndpointProvider)
    {
        $this->EndpointProvider = $EndpointProvider;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEndpointProvider()
    {
        return $this->EndpointProvider;
    }

    /**
     * @inheritdoc
     */
    public function setServer($server)
    {
        $this->server = $server;
        $this->setAPIUrl();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @inheritdoc
     */
    protected function setAPIUrl()
    {
        $this->apiURL = $this->server;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAPIUrl()
    {
        return $this->apiURL;
    }

    /**
     * @inheritdoc
     */
    public function setVersion($version)
    {
        $this->version = $version;
        $this->setAPIUrl();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->currentEndPoint;
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->lastEndPoint;
    }

    /**
     * @inheritdoc
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        if (isset($this->EndpointProvider)){
            $this->setCurrentEndpoint($this->EndpointProvider->getEndpoint($name,$this->version))
                ->current()
                    ->setBaseUrl($this->apiURL)
                    ->setOptions($arguments);
            return $this->currentEndPoint;
        }else{
            throw new EndpointProviderMissing();
        }
    }

    /**
     * @inheritdoc
     */
    protected function setCurrentEndpoint(EndpointInterface $Endpoint)
    {
        if (isset($this->currentEndPoint)){
            unset($this->lastEndPoint);
            $this->lastEndPoint = $this->currentEndPoint;
            unset($this->currentEndPoint);
        }
        $this->currentEndPoint = $Endpoint;
        return $this;
    }

}
