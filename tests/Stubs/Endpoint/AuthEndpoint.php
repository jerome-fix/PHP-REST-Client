<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\JSON\Endpoint;

class AuthEndpoint extends Endpoint
{
    protected static $_ENDPOINT_URL = 'authenticate';

    protected static $_DEFAULT_PROPERTIES = array(
        'httpMethod' => JSON::HTTP_POST
    );
}