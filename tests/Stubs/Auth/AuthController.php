<?php

namespace MRussell\REST\Tests\Stubs\Auth;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Auth\Abstracts\AbstractAuthController;

class AuthController extends AbstractAuthController {
    protected $token = '12345';

    public function configureRequest(Request $Request): Request {
        $body = $Request->getBody();
        $body['token'] = $this->token;
        return $Request->withBody($body);
    }

    public function parseResponseToToken(string $action, Response $response)
    {
        return $response->getBody()->getContents();
    }

}
