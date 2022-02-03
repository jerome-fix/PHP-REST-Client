<?php

namespace MRussell\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;

trait JsonHandlerTrait {

    public function configureRequest(Request $request, $data): Request {
        return parent::configureRequest($request, $data)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Return JSON Decoded response body
     * @param $associative
     * @return mixed
     */
    public function getResponseBody($associative = true) {
        $body = $this->getResponse()->getBody()->getContents();
        try {
            $body = json_decode($body, $associative);
        }catch (\Exception $e){}

        return $body;
    }
}
