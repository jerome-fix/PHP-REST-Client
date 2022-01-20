<?php

namespace MRussell\REST\Client;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Endpoint\Provider\EndpointProviderInterface;
use MRussell\REST\Exception\Client\EndpointProviderMissing;
use GuzzleHttp\Psr7\Request;

/**
 * A Generic Abstract Client
 * @package MRussell\REST\Client\Abstracts\AbstractClient
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var HandlerStack
     */
    private $clientHandlerStack;

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

    public function __construct()
    {
        $this->initHttpClient();
    }

    /**
     * @return void
     */
    protected function initHttpClient()
    {
        $this->clientHandlerStack = HandlerStack::create();
        $this->httpClient = new Client(['handler' => $this->clientHandlerStack]);
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @return HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->clientHandlerStack;
    }

    /**
     * @inheritdoc
     */
    public function setAuth(AuthControllerInterface $Auth): ClientInterface
    {
        $this->Auth = $Auth;
        $this->getHandlerStack()->push(Middleware::mapRequest(function (Request $request)  use ($Auth){
            return $Auth->configureRequest($request);
        }),'configureAuth');
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAuth(): AuthControllerInterface
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
    public function __call($name, $arguments)
    {
        if (isset($this->EndpointProvider)){
            $this->setCurrentEndpoint($this->EndpointProvider->getEndpoint($name,$this->version))
                ->current()
                    ->setBaseUrl($this->apiURL)
                    ->setUrlArgs($arguments)
                    ->setHttpClient($this->getHttpClient());
            return $this->currentEndPoint;
        }else{
            throw new EndpointProviderMissing();
        }
    }

    /**
     * Rotates current Endpoint to Last Endpoint, and sets Current Endpoint with passed in Endpoint
     * @param EndpointInterface $Endpoint
     * @return self
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
