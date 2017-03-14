<?php

namespace MRussell\REST\Exception\Endpoint;

class RegistrationException extends Exception {

    protected $message = 'Endpoint Object [%s] must extend MRussell\REST\Endpoint\EndpointInterface';

}