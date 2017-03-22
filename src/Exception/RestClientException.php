<?php

namespace MRussell\REST\Exception;

class RestClientException extends \Exception
{
    protected $message = 'An Unknown Exception occurred in the REST Client Framework';

    public function __construct($args = array()) {
        if (!empty($args)){
            $this->message = vsprintf($this->message,$args);
        }
        parent::__construct($this->message);
    }
}