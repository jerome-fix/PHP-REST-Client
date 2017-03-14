<?php

namespace MRussell\REST\Endpoint\Interfaces;

use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Response\ResponseInterface;
use MRussell\REST\Endpoint\Data\DataInterface;

interface EndpointInterface
{
    /**
     * Set the urlOptions property to configure the URL variables
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options);

    /**
     * Get the configured Url Options
     * @return mixed
     */
    public function getOptions();

    /**
     * Set the Properties that define the API Endpoint
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties);

    /**
     * Set the Properties that define the API Endpoint
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setProperty($name,$value);

    /**
     * Set the Properties that define the API Endpoint
     * @return array
     */
    public function getProperties();

    /**
     * Sets the data on the Endpoint Object, that will be passed to Request Object
     * @param DataInterface $data
     * @return $this
     */
    public function setData(DataInterface $data);

    /**
     * Set the Base URL that the Endpoint uses in regards to it's pre-configured Endpoint URL
     * @param $url
     * @return $this
     */
    public function setBaseUrl($url);

    /**
     * Get the Base URL that is currently configured on the Endpoint
     * @return mixed
     */
    public function getBaseUrl();

    /**
     * Get the Relative URL for the API Endpoint
     * @return mixed
     */
    public function getEndPointUrl();

    /**
     * Set the Request Object used by the Endpoint
     * @param RequestInterface $Request
     * @return $this
     */
    public function setRequest(RequestInterface $Request);

    /**
     * Set the Response Object used by the Endpoint
     * @param ResponseInterface $Response
     * @return $this
     */
    public function setResponse(ResponseInterface $Response);

    /**
     * Execute the Endpoint Object using the desired action
     * @return $this
     */
    public function execute();

    /**
     * Get the data URL being used by the Endpoint
     * @return string
     */
    public function getData();

    /**
     * Get the Request Object being used by the Endpoint
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Get the Response Object being used by the Endpoint
     * @return ResponseInterface
     */
    public function getResponse();

}
