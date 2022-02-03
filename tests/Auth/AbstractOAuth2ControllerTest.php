<?php

namespace MRussell\REST\Tests\Auth;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Tests\Stubs\Auth\OAuth2Controller;
use MRussell\REST\Tests\Stubs\Client\Client;
use MRussell\REST\Tests\Stubs\Endpoint\AuthEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\RefreshEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractOAuth2ControllerTest
 * @package MRussell\REST\Tests\Auth
 * @coversDefaultClass MRussell\REST\Auth\Abstracts\AbstractOAuth2Controller
 * @group AbstractOAuth2ControllerTest
 * @group Auth
 */
class AbstractOAuth2ControllerTest extends TestCase {
    /**
     * @var Client
     */
    protected static $client;


    public static function setUpBeforeClass(): void {
        //Add Setup for static properties here
        self::$client = new Client();
    }

    public static function tearDownAfterClass(): void {
        //Add Tear Down for static properties here
    }

    protected $token = array(
        'access_token' => '12345',
        'refresh_token' => '67890',
        'expires_in' => 3600
    );

    protected $credentials = array(
        'client_id' => 'test',
        'client_secret' => 's3cr3t'
    );

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::oauthHeader
     */
    public function testOAuthHeader() {
        $this->assertEquals('Authorization', OAuth2Controller::oauthHeader());
        $this->assertEquals('Test', OAuth2Controller::oauthHeader('Test'));
        $this->assertEquals('Test', OAuth2Controller::oauthHeader());
        $Auth = new OAuth2Controller();
        $this->assertEquals('Test', $Auth->oauthHeader());
        $this->assertEquals('Authorization', $Auth->oauthHeader('Authorization'));
        $this->assertEquals('Authorization', $Auth->oauthHeader());
        $this->assertEquals('Authorization', OAuth2Controller::oauthHeader());
    }

    /**
     * @covers ::setToken
     * @covers ::configureToken
     * @covers ::isAuthenticated
     * @covers ::isTokenExpired
     */
    public function testSetToken() {
        $Auth = new OAuth2Controller();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\OAuth2Controller');
        $setToken = $Class->getMethod('setToken');
        $setToken->setAccessible(true);
        $isTokenExpired = $Class->getMethod('isTokenExpired');
        $isTokenExpired->setAccessible(true);
        $this->assertEquals($Auth, $setToken->invoke($Auth, $this->token));
        $newToken = $Auth->getToken();
        $this->assertNotEmpty($newToken['expiration']);
        $this->assertEquals(true, ($newToken['expiration'] >= time() + 3570));
        $this->assertEquals(true, $Auth->isAuthenticated());

        $newToken = $this->token;
        $newToken['expires_in'] = -1;
        $this->assertEquals($Auth, $setToken->invoke($Auth, $newToken));
        $newToken = $Auth->getToken();
        $this->assertNotEmpty($newToken['expiration']);
        $this->assertEquals(false, $Auth->isAuthenticated());
        $this->assertEquals(true, $isTokenExpired->invoke($Auth));

        unset($newToken['expires_in']);
        unset($newToken['expiration']);
        $this->assertEquals($Auth, $setToken->invoke($Auth, $newToken));
        $newToken = $Auth->getToken();
        $this->assertEquals(false, isset($newToken['expiration']));
        $this->assertEquals(-1, $isTokenExpired->invoke($Auth));
    }

    /**
     * @covers ::setToken
     * @throws MRussell\REST\Exception\Auth\InvalidToken
     */
    public function testInvalidToken() {
        $Auth = new OAuth2Controller();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\OAuth2Controller');
        $setToken = $Class->getMethod('setToken');
        $this->expectException(\MRussell\REST\Exception\Auth\InvalidToken::class);
        $setToken->setAccessible(true);
        $setToken->invoke($Auth, []);
    }

    /**
     * @depends testSetToken
     * @covers ::configureRequest
     * @covers ::getAuthHeaderValue
     */
    public function testConfigure() {
        $Auth = new OAuth2Controller();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\AuthController');
        $Request = new Request("POST", "");
        $Auth->configureRequest($Request);
        $setToken = $Class->getMethod('setToken');
        $setToken->setAccessible(true);
        $this->assertEquals($Auth, $setToken->invoke($Auth, $this->token));
        $Request = $Auth->configureRequest($Request);
        $headers = $Request->getHeaders();
        $this->assertNotEmpty($headers['Authorization']);
        $this->assertEquals(['Bearer 12345'], $headers['Authorization']);
    }

    /**
     * @covers ::refresh
     * @throws \MRussell\REST\Exception\Auth\InvalidToken
     */
    public function testRefresh() {
        $Auth = new OAuth2Controller();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\OAuth2Controller');
        $setToken = $Class->getMethod('setToken');
        $setToken->setAccessible(true);
        $Auth->setCredentials($this->credentials);
        $setToken->invoke($Auth, $this->token);
        $RefreshEndpoint = new RefreshEndpoint();
        self::$client->mockResponses->append(new Response(200));
        $RefreshEndpoint->setHttpClient(self::$client->getHttpClient());
        $Auth->setActionEndpoint(OAuth2Controller::ACTION_OAUTH_REFRESH, $RefreshEndpoint);
        $this->expectException(\MRussell\REST\Exception\Auth\InvalidToken::class);
        $this->assertEquals(false, $Auth->refresh());
        $newToken = $this->token;
        unset($newToken['refresh_token']);
        $setToken->invoke($Auth, $newToken);
        $this->assertEquals(false, $Auth->refresh());
    }

    /**
     * @covers ::configureEndpoint
     * @covers ::configureRefreshEndpoint
     * @covers ::configureAuthenticationEndpoint
     */
    public function testConfigureData() {
        $Auth = new OAuth2Controller();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\OAuth2Controller');
        $setToken = $Class->getMethod('setToken');
        $setToken->setAccessible(true);
        $Auth->setCredentials($this->credentials);
        $setToken->invoke($Auth, $this->token);

        $AuthEndpoint = new AuthEndpoint();
        $AuthEndpoint->setBaseUrl('localhost');
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\OAuth2Controller');
        $method = $Class->getMethod('configureEndpoint');
        $method->setAccessible(true);
        $AuthEndpoint = $method->invoke($Auth, $AuthEndpoint, OAuth2Controller::ACTION_AUTH);
        $data = $AuthEndpoint->getData();
        $this->assertEquals(OAuth2Controller::OAUTH_CLIENT_CREDENTIALS_GRANT, $data['grant_type']);

        $RefreshEndpoint = new RefreshEndpoint();
        $RefreshEndpoint->setBaseUrl('localhost');
        $RefreshEndpoint = $method->invoke($Auth, $RefreshEndpoint, OAuth2Controller::ACTION_OAUTH_REFRESH);
        $data = $RefreshEndpoint->getData();
        $this->assertEquals(OAuth2Controller::OAUTH_REFRESH_GRANT, $data['grant_type']);
        $this->assertEquals('test', $data['client_id']);
        $this->assertEquals('s3cr3t', $data['client_secret']);
        $this->assertEquals('67890', $data['refresh_token']);
        return $Auth;
    }
}
