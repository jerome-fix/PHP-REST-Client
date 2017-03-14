<?php

namespace MRussell\REST\Exception\Endpoint;

class RequiredDataException extends \Exception
{
    protected $message = "Missing or Invalid data on Endpoint [%s].";

}
