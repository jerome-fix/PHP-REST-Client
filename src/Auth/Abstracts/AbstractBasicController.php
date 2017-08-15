<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\Http\Request\RequestInterface;

/**
 * Class AbstractBasicController
 * @package MRussell\REST\Auth\Abstracts
 */
class AbstractBasicController extends AbstractAuthController
{
    const DEFAULT_AUTH_HEADER = 'Authorization';

    const DEFAULT_AUTH_TYPE = 'Basic';

    protected static $_AUTH_HEADER = self::DEFAULT_AUTH_HEADER;

    protected static $_AUTH_TYPE = self::DEFAULT_AUTH_TYPE;

    /**
     * @inheritdoc
     */
    public function configureRequest(RequestInterface $Request)
    {
        $Request->addHeader(static::$_AUTH_HEADER, $this->getAuthHeaderValue());
        return $this;
    }

    /**
     * Parse the Credentials or Token to build out the Auth Header Value
     * @return string
     */
    protected function getAuthHeaderValue()
    {
        $value = "";
        if (isset($this->credentials['username']) && isset($this->credentials['password'])){
            $value = $this->credentials['username'].":".$this->credentials['password'];
            $value = base64_encode($value);
        }
        return static::$_AUTH_TYPE." ".$value;
    }
}