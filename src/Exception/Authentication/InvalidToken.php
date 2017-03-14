<?php

namespace MRussell\REST\Exception\Authentication;


class InvalidToken extends AuthException {

    protected $message = 'An Invalid Token was attempted to be set on the Auth Controller.';

}