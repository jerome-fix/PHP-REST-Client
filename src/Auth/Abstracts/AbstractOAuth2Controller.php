<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\Http\Request\RequestInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Auth\InvalidToken;

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
     * The OAuth2 Full token
     * @var array
     */
    protected $token = array();

    /**
     * Set the OAuth Authorization header
     * @param $header
     * @return $this
     */
    public static function oauthHeader($header = null)
    {
        if ($header !== null) {
            static::$_OAUTH_HEADER = $header;
        }
        return static::$_OAUTH_HEADER;
    }

    /**
     * @inheritdoc
     * @throws InvalidToken
     */
    protected function setToken($token)
    {
        if (is_array($token) && isset($token['access_token'])) {
            $token = $this->configureToken($token);
            return parent::setToken($token);
        }
        throw new InvalidToken();
    }

    /**
     * Configure the Expiration property on the token, based on the 'expires_in' property
     * @param $token
     * @return mixed
     */
    protected function configureToken($token)
    {
        if (isset($token['expires_in'])) {
            $token['expiration'] = time() + ($token['expires_in'] - 30);
        }
        return $token;
    }

    /**
     * @inheritdoc
     */
    public function configureRequest(RequestInterface $Request)
    {
        if ($this->isAuthenticated()) {
            $Request->addHeader(static::$_OAUTH_HEADER, sprintf(static::$_TOKEN_VALUE, $this->token['access_token']));
        }
        return $this;
    }

    /**
     * Refreshes the OAuth 2 Token
     * @return bool
     * @throws InvalidToken
     */
    public function refresh()
    {
        if (isset($this->token['refresh_token'])) {
            $Endpoint = $this->getActionEndpoint(self::ACTION_OAUTH_REFRESH);
            if ($Endpoint !== null) {
                $Endpoint = $this->configureEndpoint($Endpoint, self::ACTION_OAUTH_REFRESH);
                $response = $Endpoint->execute()->getResponse();
                if ($response->getStatus() == '200') {
                    $this->setToken($response->getBody());
                    return true;
                }
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
        if (parent::isAuthenticated()) {
            if (isset($this->token['access_token'])) {
                $expired = $this->isTokenExpired();
                //We err on the side of valid vs invalid, as the API will invalidate if we are wrong, which isn't harmful
                if ($expired === false || $expired === -1) {
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
    protected function isTokenExpired()
    {
        if (isset($this->token['expiration'])) {
            if (time() > $this->token['expiration']) {
                return true;
            } else {
                return false;
            }
        }
        return -1;
    }

    /**
     * @inheritdoc
     */
    protected function configureEndpoint(EndpointInterface $Endpoint, $action)
    {
        switch($action){
            case self::ACTION_OAUTH_REFRESH:
                return $this->configureRefreshEndpoint($Endpoint);
            default:
                return parent::configureEndpoint($Endpoint, $action);
        }
    }

    /**
     * Configure the Refresh Data based on Creds, Token, and Refresh Grant Type
     * @param EndpointInterface $Endpoint
     * @return EndpointInterface
     */
    protected function configureRefreshEndpoint(EndpointInterface $Endpoint)
    {
        $data = array();
        $data['client_id'] = $this->credentials['client_id'];
        $data['client_secret'] = $this->credentials['client_secret'];
        $data['grant_type'] = self::OAUTH_REFRESH_GRANT;
        $data['refresh_token'] = $this->token['refresh_token'];
        return $Endpoint->setData($data);
    }

    /**
     * Add OAuth Grant Type for Auth
     * @inheritdoc
     */
    protected function configureAuthenticationEndpoint(EndpointInterface $Endpoint)
    {
        $data = $this->credentials;
        $data['grant_type'] = static::$_DEFAULT_GRANT_TYPE;
        return $Endpoint->setData($data);
    }

}