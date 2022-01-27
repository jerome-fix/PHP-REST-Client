<?php

namespace MRussell\REST\Tests\Stubs\Auth;

use GuzzleHttp\Psr7\Request;
use MRussell\REST\Auth\Abstracts\AbstractAuthController;

class AuthController extends AbstractAuthController
{
    protected $token = '12345';

    public function configureRequest(Request $Request): Request {
        $body = $Request->getBody();
        $body['token'] = $this->token;
        $Request->withBody($body);
        return $Request;
    }
}