<?php

namespace MRussell\REST\Endpoint\JSON;

use MRussell\Http\Request\JSON as JSONRequest;
use MRussell\Http\Response\JSON as JSONResponse;
use MRussell\REST\Endpoint\Abstracts\AbstractSmartEndpoint;

class SmartEndpoint extends AbstractSmartEndpoint
{
    protected static $_DATA_CLASS = 'MRussell\\REST\\Endpoint\\Data\\EndpointData';

    public function __construct(array $options = array(), array $properties = array()) {
        parent::__construct($options, $properties);
        $Request = new JSONRequest();
        $this->setRequest($Request);
        $Response = new JSONResponse();
        $this->setResponse($Response);
    }
}