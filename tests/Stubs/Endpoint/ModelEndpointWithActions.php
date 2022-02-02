<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint;

class ModelEndpointWithActions extends AbstractModelEndpoint {
    protected $actions = array(
        'foo' => "GET",
    );
}
