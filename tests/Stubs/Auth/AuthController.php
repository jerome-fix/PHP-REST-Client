<?php

namespace MRussell\REST\Tests\Stubs\Auth;

use MRussell\Http\Request\RequestInterface;
use MRussell\REST\Auth\Abstracts\AbstractAuthController;

class AuthController extends AbstractAuthController
{
    protected $token = '12345';

    public function configureRequest(RequestInterface $Request) {
        $body = $Request->getBody();
        $body['token'] = $this->token;
        $Request->setBody($body);
        return $this;
    }
}