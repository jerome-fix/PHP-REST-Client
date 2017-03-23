<?php

namespace MRussell\REST\Tests\Stubs\Endpoint;

use MRussell\REST\Endpoint\Abstracts\AbstractSmartEndpoint;

class SmartEndpoint extends AbstractSmartEndpoint
{
    protected static $_DATA_CLASS = 'MRussell\REST\Endpoint\Data\EndpointData';
}