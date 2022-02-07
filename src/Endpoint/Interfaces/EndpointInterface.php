<?php

namespace MRussell\REST\Endpoint\Interfaces;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

interface EndpointInterface extends PropertiesInterface, ResettableInterface {
    /**
     * Set the urlArgs property to configure the URL variables
     * @param array $args
     * @return $this
     */
    public function setUrlArgs(array $args);

    /**
     * Get the configured Url Arguments
     * @return array
     */
    public function getUrlArgs(): array;

    /**
     * Sets the data on the Endpoint Object, that will be passed to Request Object
     * @param mixed $data
     * @return $this
     */
    public function setData($data);

    /**
     * Get the data being used by the Endpoint
     * @return array|\ArrayAccess
     */
    public function getData();

    /**
     * Set the Base URL that the Endpoint uses in regards to it's pre-configured Endpoint URL
     * @param string $url
     * @return $this
     */
    public function setBaseUrl(string $url);

    /**
     * Set the Guzzle HTTP Client to utilize sending requests
     * @param Client $client
     * @return $this
     */
    public function setHttpClient(Client $client);

    /**
     * Get the Base URL that is currently configured on the Endpoint
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * Get the Relative URL for the API Endpoint
     * @return string
     */
    public function getEndPointUrl(): string;

    /**
     * Execute the Endpoint Object using the desired action
     * @return $this
     */
    public function execute();

    /**
     * Get the Request Object being used by the Endpoint
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Get the Response Object being used by the Endpoint
     * @return Response
     */
    public function getResponse(): Response;

    /**
     * Check if authentication is required for use of the Endpoint
     * @return bool
     */
    public function authRequired(): bool;
}
