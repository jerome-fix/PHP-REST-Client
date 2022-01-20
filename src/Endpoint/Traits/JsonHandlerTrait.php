<?php

namespace MRussell\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;

trait JsonHandlerTrait
{

    public function configureRequest(Request $request,$data): Request
    {
        return parent::configureRequest($request,$data)->withHeader('Content-Type','application/json');
    }

    /**
     * Return JSON Decoded response body
     * @param $associative
     * @return mixed
     */
    public function getResponseBody($associative = true)
    {
        return json_decode($this->getResponse()->getBody()->getContents(),$associative);
    }
}