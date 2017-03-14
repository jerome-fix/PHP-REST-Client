<?php
/**
 * Created by PhpStorm.
 * User: mrussell
 * Date: 1/20/17
 * Time: 11:55 AM
 */

namespace MRussell\REST\Exception\Authentication;


class NotAuthenticated extends AuthException
{
    protected $message = 'Auth Controller not currently authenticated.';
}