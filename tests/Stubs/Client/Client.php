<?php

namespace MRussell\REST\Tests\Stubs\Client;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MRussell\REST\Client\AbstractClient;

class Client extends AbstractClient {
    public $mockResponses;
    public function __construct() {

        $this->mockResponses = new MockHandler([]);
        $this->setHandlerStack(HandlerStack::create($this->mockResponses));
    }
}
