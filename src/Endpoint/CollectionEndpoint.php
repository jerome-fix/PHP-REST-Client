<?php

namespace MRussell\REST\Endpoint;

use MRussell\REST\Endpoint\Abstracts\AbstractCollectionEndpoint;
use MRussell\REST\Endpoint\Traits\JsonHandlerTrait;

class CollectionEndpoint extends AbstractCollectionEndpoint {
    use JsonHandlerTrait;

    protected static $_MODEL_CLASS = ModelEndpoint::class;
}
