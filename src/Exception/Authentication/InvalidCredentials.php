<?php

namespace MRussell\REST\Exception\Authentication;


class InvalidCredentials extends AuthException
{
    protected $message = 'Invalid credentials provided for Auth Controller.';
}