<?php

namespace MRussell\REST\Auth;

use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Authentication\InvalidToken;
use MRussell\REST\Exception\Authentication\NotAuthenticated;

abstract class AbstractOAuth2Controller extends AbstractAuthController
{

    /**
     * @var string
     */
    protected static $_OAUTH_HEADER = 'Authorization';

    /**
     * @var string
     */
    protected static $_REFRESH_ENDPOINT_CLASS = '';

    /**
     * @var EndpointInterface
     */
    protected $Oauth_RefreshEndpoint;


    public function __construct() {
        parent::__construct();
        if (static::$_REFRESH_ENDPOINT_CLASS !== ''){
            $this->setRefreshEndpoint(new static::$_REFRESH_ENDPOINT_CLASS());
        }
    }

    /**
     * Set the OAuth Authorization header
     * @param $header
     * @return $this
     */
    public static function oauthHeader($header = NULL){
        if ($header !== NULL){
            static::$_OAUTH_HEADER = $header;
        }
        return static::$_OAUTH_HEADER;
    }

    /**
     * @inheritdoc
     * @throws InvalidToken
     */
    protected function setToken($token) {
        if (is_object($token) && isset($token->access_token)){
            $token = $this->configureToken($token);
            parent::setToken($token);
        } else {
            throw new InvalidToken();
        }
    }

    /**
     * Configure the Expiration property on the token, based on the 'expires_in' property
     * @param $token
     * @return mixed
     */
    protected function configureToken($token){
        if (isset($token->expires_in)){
            $token->expiration = time() + ($token->expires_in - 30);
        }
        return $token;
    }

    /**
     * @inheritdoc
     * @throws NotAuthenticated
     */
    public function configure(EndpointInterface $Endpoint) {
        if ($this->isAuthenticated()) {
            $Endpoint->getRequest()->addHeader(static::$_OAUTH_HEADER, "Bearer ".$this->token->access_token);
            return $this;
        } else {
            throw new NotAuthenticated();
        }
    }

    /**
     * Set the Refresh Endpoint
     * @param EndpointInterface $Endpoint
     * @return $this
     */
    public function setRefreshEndpoint(EndpointInterface $Endpoint)
    {
        $this->Oauth_RefreshEndpoint = $Endpoint;
        return $this;
    }

    /**
     * Refreshes the OAuth 2 Token
     * @return bool
     * @throws InvalidToken
     * @throws NotAuthenticated
     */
    public function refresh()
    {
        $this->configure($this->Oauth_RefreshEndpoint);
        $response = $this->Oauth_RefreshEndpoint->execute()->getRequest()->getResponse();
        if ($response->getStatus() == '200'){
            $this->setToken($response->getBody(true));
            return true;
        }
        return false;
    }

    /**
     * Checks for Access Token property in token, and checks if Token is expired
     * @inheritdoc
     */
    public function isAuthenticated() {
        if (parent::isAuthenticated()){
            if (isset($this->token->access_token)){
                $expired = $this->isTokenExpired();
                //We err on the side of valid vs invalid, as the API will invalidate if we are wrong, which isn't harmful
                if ($expired === FALSE || $expired === -1){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks if Token is expired based on 'expiration' flag on token
     * - Returns -1 if no expiration property is found
     * @return bool|int
     */
    protected function isTokenExpired(){
        if (isset($this->token->expiration)){
            if (time() > $this->token->expiration){
                return true;
            }else{
                return false;
            }
        }
        return -1;
    }


}