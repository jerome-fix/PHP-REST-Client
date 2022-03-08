<?php

namespace MRussell\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;

trait JsonHandlerTrait {

    protected $respBody = null;

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
    public function getResponseBody($associative = true)
    {
        if (!$this->respBody) {
            $this->respBody = $this->getResponse()->getBody()->getContents();
        }
        $body = null;
        try {
            $body = json_decode($this->respBody, $associative);
        } catch (\Exception $e) {
        }
        return $body;
    }
}
