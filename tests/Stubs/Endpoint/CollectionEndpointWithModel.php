<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\REST\Endpoint\Abstracts\AbstractCollectionEndpoint;

class CollectionEndpointWithModel extends AbstractCollectionEndpoint {
    protected static $_ENDPOINT_URL = 'accounts';
    protected static $_MODEL_CLASS = 'MRussell\\REST\\Tests\\Stubs\\Endpoint\\ModelEndpoint';
}
