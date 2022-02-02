<?php

namespace MRussell\REST\Endpoint\Interfaces;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

interface EndpointInterface {
    /**
     * Set the urlArgs property to configure the URL variables
     * @param array $args
     * @return self
     */
    public function setUrlArgs(array $args): self;

    /**
     * Get the configured Url Arguments
     * @return array
     */
    public function getUrlArgs(): array;

    /**
     * Set the Properties that define the API Endpoint
     * @param array $properties
     */
    public function setProperties(array $properties): void;

    /**
     * Set the Properties that define the API Endpoint
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setProperty(string $name, $value): self;

    /**
     * Set the Properties that define the API Endpoint
     * @return array
     */
    public function getProperties(): array;

    /**
     * Sets the data on the Endpoint Object, that will be passed to Request Object
     * @param mixed $data
     * @return self
     */
    public function setData($data): self;

    /**
     * Get the data being used by the Endpoint
     * @return array|\ArrayAccess
     */
    public function getData();

    /**
     * Set the Base URL that the Endpoint uses in regards to it's pre-configured Endpoint URL
     * @param string $url
     * @return self
     */
    public function setBaseUrl(string $url): self;

    /**
     * Set the Guzzle HTTP Client to utilize sending requests
     * @param Client $client
     * @return self
     */
    public function setHttpClient(Client $client): self;

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
     * @return self
     */
    public function execute(): self;

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
