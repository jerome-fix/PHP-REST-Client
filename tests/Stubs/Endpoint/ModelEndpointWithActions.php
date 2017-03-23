<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\Http\Request\Curl;
use MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint;

class ModelEndpointWithActions extends AbstractModelEndpoint
{
    protected $actions = array(
        'foo' => Curl::HTTP_GET
    );
}