<?php
/**
 * Created by PhpStorm.
 * User: mrussell
 * Date: 1/19/17
 * Time: 10:35 PM
 */

namespace MRussell\REST\Exception\Endpoint;


class UnknownEndpoint extends Exception
{
    protected $message = 'An Unknown Endpoint [%s] was requested.';


}