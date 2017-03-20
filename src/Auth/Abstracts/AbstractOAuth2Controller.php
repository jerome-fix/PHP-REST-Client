<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\Http\Request\RequestInterface;
use MRussell\REST\Endpoint\Data\EndpointData;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Authentication\InvalidToken;
use MRussell\REST\Exception\Authentication\NotAuthenticated;

abstract class AbstractOAuth2Controller extends AbstractAuthController
{
    const ACTION_OAUTH_REFRESH = 'refresh';

    const OAUTH_RESOURCE_OWNER_GRANT = 'password';

    const OAUTH_CLIENT_CREDENTIALS_GRANT = 'client_credentials';

    const OAUTH_AUTHORIZATION_CODE_GRANT = 'authorization_code';

    const OAUTH_REFRESH_GRANT = 'refresh_token';

    /**
     * @var string
     */
    protected static $_OAUTH_HEADER = 'Authorization';

    /**
     * @var string
     */
    protected static $_DEFAULT_GRANT_TYPE = self::OAUTH_CLIENT_CREDENTIALS_GRANT;

    /**
     * @var string
     */
    protected static $_TOKEN_VALUE = 'Bearer %s';

    protected $actions = array(
        self::ACTION_OAUTH_REFRESH
    );

    /**
     *
     * @var array
     */
    protected $token = array();

    /**
     * Set the OAuth Authorization header
     * @param $header
     * @return $this
     */
    public static function oauthHeader($header = NULL)
    {
        if ($header !== NULL){
            static::$_OAUTH_HEADER = $header;
        }
        return static::$_OAUTH_HEADER;
    }

    /**
     * @inheritdoc
     * @throws InvalidToken
     */
    protected function setToken(array $token)
    {
        if (isset($token['access_token'])){
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
    protected function configureToken($token)
    {
        if (isset($token['expires_in'])){
            $token['expiration'] = time() + ($token['expires_in'] - 30);
        }
        return $token;
    }

    /**
     * @inheritdoc
     * @throws NotAuthenticated
     */
    public function configure(RequestInterface $Request)
    {
        if ($this->isAuthenticated()) {
            $Request->addHeader(static::$_OAUTH_HEADER, sprintf(static::$_TOKEN_VALUE,$this->token['access_token']));
            return $this;
        } else {
            throw new NotAuthenticated();
        }
    }

    /**
     * Refreshes the OAuth 2 Token
     * @return bool
     * @throws InvalidToken
     * @throws NotAuthenticated
     */
    public function refresh()
    {
        if (isset($this->token['refresh_token'])){
            $Endpoint = $this->configureData(self::ACTION_OAUTH_REFRESH);
            $response = $Endpoint->execute()->getResponse();
            if ($response->getStatus() == '200'){
                $this->clearToken();
                return true;
            }
        }
        return false;
    }

    /**
     * Checks for Access Token property in token, and checks if Token is expired
     * @inheritdoc
     */
    public function isAuthenticated()
    {
        if (parent::isAuthenticated()){
            if (isset($this->token['access_token'])){
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
        if (isset($this->token['expiration'])){
            if (time() > $this->token['expiration']){
                return true;
            }else{
                return false;
            }
        }
        return -1;
    }

    protected function configureData($action) {
        if ($action == self::ACTION_OAUTH_REFRESH){
            $Endpoint = $this->getActionEndpoint($action);
            return $this->configureRefreshData($Endpoint);
        }
        return parent::configureData($action);
    }

    /**
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureRefreshData(EndpointInterface $Endpoint){
        $data = array();
        $data['client_id'] = $this->credentials['client_id'];
        $data['client_secret'] = $this->credentials['client_secret'];
        $data['grant_type'] = self::OAUTH_REFRESH_GRANT;
        $data['refresh_token'] = $this->token['refresh_token'];
        return $Endpoint->setData($data);
    }

    protected function configureAuthenticationData(EndpointInterface $Endpoint) {
        $data = $this->credentials;
        $data['grant_type'] = static::$_DEFAULT_GRANT_TYPE;
        return $Endpoint->setData($data);
    }
    
}