<?php

namespace MRussell\REST\Exception\Endpoint;

class Exception extends \Exception
{
    public function __construct($className) {
        parent::__construct(sprintf($this->message,$className));
    }
}