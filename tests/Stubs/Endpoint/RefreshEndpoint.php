<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\JSON\Endpoint;

class RefreshEndpoint extends Endpoint
{
    protected static $_ENDPOINT_URL = 'refresh';

    protected static $_DEFAULT_PROPERTIES = array(
        'httpMethod' => "POST"
    );
}