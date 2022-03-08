<?php

namespace MRussell\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;

trait JsonHandlerTrait {

    /**
     * @param Request $request
     * @return Request
     */
    protected function configureJsonRequest(Request $request): Request {
        return $request->withHeader('Content-Type', 'application/json');
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
