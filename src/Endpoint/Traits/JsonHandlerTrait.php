<?php

namespace MRussell\REST\Endpoint\Traits;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait JsonHandlerTrait {

    protected $respContent = null;

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
    public function getResponseContent(Response $response,$associative = true)
    {
        if (!$this->respContent) {
            $this->respContent = $response->getBody()->getContents();
            $response->getBody()->rewind();
            $contentType = $response->getHeader('Content-Type');
            $contentType = is_array($contentType)?"":$contentType;
            if (strpos($contentType,"json") == FALSE){
                $this->respContent = html_entity_decode($this->respContent,ENT_QUOTES|ENT_HTML5,'UTF-8');
            }
        }
        $body = null;
        try {
            $body = json_decode($this->respContent, $associative);
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }
        // @codeCoverageIgnoreEnd
        return $body;
    }
}
