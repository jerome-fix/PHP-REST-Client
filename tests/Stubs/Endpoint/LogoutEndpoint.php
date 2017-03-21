<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\JSON\Endpoint;

class LogoutEndpoint extends Endpoint
{
    protected static $_ENDPOINT_URL = 'logout';

    protected static $_DEFAULT_PROPERTIES = array(
        'httpMethod' => JSON::HTTP_POST
    );
}