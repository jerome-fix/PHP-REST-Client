<?php
/**
 * Created by PhpStorm.
 * User: mrussell
 * Date: 1/20/17
 * Time: 10:51 AM
 */

namespace MRussell\REST\Exception\Client;


class ClientException extends \Exception {

    protected $message = 'Unknown REST Client Exception occurred.';

}