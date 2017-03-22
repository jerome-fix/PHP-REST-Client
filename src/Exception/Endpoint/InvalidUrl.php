<?php
/**
 * User: mrussell
 * Date: 3/22/17
 * Time: 3:09 PM
 */

namespace MRussell\REST\Exception\Endpoint;


class InvalidUrl extends EndpointException
{
    protected $message = 'Invalid url configuration on Endpoint [%s]: %s';
}