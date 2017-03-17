<?php

namespace MRussell\REST\Endpoint\JSON;

use MRussell\Http\Request\JSON as JSONRequest;
use MRussell\Http\Response\JSON as JSONResponse;
use MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint;

class ModelEndpoint extends AbstractModelEndpoint
{
    public function __construct(array $options, array $properties) {
        parent::__construct($options, $properties);
        $Request = new JSONRequest();
        $this->setRequest($Request);
        $Response = new JSONResponse();
        $this->setResponse($Response);
    }
}