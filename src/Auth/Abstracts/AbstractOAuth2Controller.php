<?php

namespace MRussell\REST\Auth\Abstracts;

use MRussell\REST\Auth\AuthControllerInterface;
use GuzzleHttp\Psr7\Request;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Auth\InvalidToken;

abstract class AbstractOAuth2Controller extends AbstractBasicController {
    const DEFAULT_AUTH_TYPE = 'Bearer';

    const ACTION_OAUTH_REFRESH = 'refresh';

    const OAUTH_RESOURCE_OWNER_GRANT = 'password';
    const OAUTH_CLIENT_CREDENTIALS_GRANT = 'client_credentials';
    const OAUTH_AUTHORIZATION_CODE_GRANT = 'authorization_code';
    const OAUTH_REFRESH_GRANT = 'refresh_token';

    /**
     * @var string
     */
    protected static $_DEFAULT_GRANT_TYPE = self::OAUTH_CLIENT_CREDENTIALS_GRANT;

    /**
     * @inheritdoc
     */
    protected static $_AUTH_TYPE = self::DEFAULT_AUTH_TYPE;

    /**
     * @inheritdoc
     */
    protected static $_DEFAULT_AUTH_ACTIONS = array(
        self::ACTION_AUTH,
        self::ACTION_LOGOUT,
        self::ACTION_OAUTH_REFRESH
    );

    /**
     * The OAuth2 Full token
     * @var array
     */
    protected $token = array();

    /**
     * @var
     */
    protected $grant_type;

    public function __construct() {
        parent::__construct();
        $this->setGrantType(static::$_DEFAULT_GRANT_TYPE);
    }

    /**
     * @param $grant_type
     * @return $this
     */
    public function setGrantType($grant_type): AuthControllerInterface {
        $this->grant_type = $grant_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrantType(): string {
        return $this->grant_type;
    }

    /**
     * Get/Set the OAuth Authorization header
     * @param $header
     * @return string
     */
    public static function oauthHeader($header = null): string {
        if ($header !== null) {
            static::$_AUTH_HEADER = $header;
        }
        return static::$_AUTH_HEADER;
    }

    /**
     * @inheritdoc
     * @throws InvalidToken
     */
    protected function setToken($token): AuthControllerInterface {
        if (is_array($token) && isset($token['access_token'])) {
            $token = $this->configureToken($token);
            return parent::setToken($token);
        } else if (is_object($token) && $token->access_token) {
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
    protected function configureToken($token) {
        if (is_array($token)) {
            if (isset($token['expires_in'])) {
                $token['expiration'] = time() + ($token['expires_in'] - 30);
            }
        } elseif (is_object($token)) {
            if (isset($token->expires_in)) {
                $token->expiration = time() + ($token['expires_in'] - 30);
            }
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function configureRequest(Request $Request): Request {
        if ($this->isAuthenticated()) {
            return parent::configureRequest($Request);
        }
        return $Request;
    }

    /**
     * Get the Value to be set on the Auth Header
     * @return mixed
     */
    protected function getAuthHeaderValue(): string {
        return static::$_AUTH_TYPE . " " . $this->token['access_token'];
    }

    /**
     * Refreshes the OAuth 2 Token
     * @return bool
     * @throws InvalidToken
     */
    public function refresh(): bool {
        if (isset($this->token['refresh_token'])) {
            $Endpoint = $this->getActionEndpoint(self::ACTION_OAUTH_REFRESH);
            if ($Endpoint !== null) {
                $Endpoint = $this->configureEndpoint($Endpoint, self::ACTION_OAUTH_REFRESH);
                $response = $Endpoint->execute()->getResponse();
                $res = $response->getStatusCode() == 200;
                if ($res) {
                    $this->setToken($response->getBody()->getContents());
                }
            }
        }
        return $res;
    }

    /**
     * Checks for Access Token property in token, and checks if Token is expired
     * @inheritdoc
     */
    public function isAuthenticated(): bool {
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
    protected function isTokenExpired(): bool|int {
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
    protected function configureEndpoint(EndpointInterface $Endpoint, $action): EndpointInterface {
        switch ($action) {
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
    protected function configureRefreshEndpoint(EndpointInterface $Endpoint): EndpointInterface {
        return $Endpoint->setData([
            'client_id' => $this->credentials['client_id'],
            'client_secret' => $this->credentials['client_secret'],
            'grant_type' => self::OAUTH_REFRESH_GRANT,
            'refresh_token' => $this->token['refresh_token'],
        ]);
    }

    /**
     * Add OAuth Grant Type for Auth
     * @inheritdoc
     */
    protected function configureAuthenticationEndpoint(EndpointInterface $Endpoint): EndpointInterface {
        $data = $this->credentials;
        $data['grant_type'] = ($this->grant_type ? $this->grant_type : static::$_DEFAULT_GRANT_TYPE);
        return $Endpoint->setData($data);
    }

    /**
     * @inheritDoc
     */
    public function reset(): AuthControllerInterface {
        $this->setGrantType(static::$_DEFAULT_GRANT_TYPE);
        return parent::reset();
    }
}
