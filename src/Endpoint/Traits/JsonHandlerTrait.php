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
            $contentType = $this->getResponse()->getHeader('Content-Type');
            $contentType = is_array($contentType)?"":$contentType;
            if (strpos($contentType,"json") == FALSE){
                $this->respBody = html_entity_decode($this->respBody,ENT_QUOTES|ENT_HTML5,'UTF-8');
            }
        }
        $body = null;
        try {
            $body = json_decode($this->respBody, $associative);
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }
        // @codeCoverageIgnoreEnd
        return $body;
    }
}
