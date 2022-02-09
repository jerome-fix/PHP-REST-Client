<?php

namespace MRussell\REST\Tests\Auth;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Exception\Auth\InvalidAuthenticationAction;
use MRussell\REST\Auth\Abstracts\AbstractAuthController;
use MRussell\REST\Storage\StaticStorage;
use MRussell\REST\Tests\Stubs\Auth\AuthController;
use MRussell\REST\Tests\Stubs\Client\Client;
use MRussell\REST\Tests\Stubs\Endpoint\AuthEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\LogoutEndpoint;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

/**
 * Class AbstractAuthControllerTest
 * @package MRussell\REST\Tests\Auth\
 * @coversDefaultClass \MRussell\REST\Auth\Abstracts\AbstractAuthController
 * @group AbstractAuthControllerTest
 * @group Auth
 */
class AbstractAuthControllerTest extends TestCase {
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


    protected $authActions = array(
        AuthController::ACTION_AUTH,
        AuthController::ACTION_LOGOUT
    );

    protected $credentials = array(
        'user' => 'foo',
        'password' => 'bar'
    );

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::getActions
     * @return AuthController
     */
    public function testConstructor(): AuthController {
        $Auth = new AuthController();
        $this->assertEquals($this->authActions, $Auth->getActions());
        $actions = $this->authActions;
        $actions[] = 'test';
        $this->assertEquals($Auth, $Auth->setActions($actions));
        $this->assertEquals($actions, $Auth->getActions());
        unset($Auth);

        $Auth = new AuthController();
        $this->assertEquals($this->authActions, $Auth->getActions());
        return $Auth;
    }

    /**
     * @depends testConstructor
     * @param AuthController $Auth
     * @covers ::setCredentials
     * @covers ::getCredentials
     * @return AuthController
     */
    public function testSetCredentials(AuthController $Auth): AuthController {
        $this->assertEquals($Auth, $Auth->setCredentials($this->credentials));
        $this->assertEquals($this->credentials, $Auth->getCredentials());
        $Auth->setCredentials(array());
        $this->assertEquals(array(), $Auth->getCredentials());
        return $Auth;
    }

    /**
     * @depends testSetCredentials
     * @param AuthController $Auth
     * @covers ::setToken
     * @covers ::getToken
     * @covers ::clearToken
     * @covers ::isAuthenticated
     * @return AuthController
     */
    public function testGetToken(AuthController $Auth): AuthController {
        $this->assertEquals('12345', $Auth->getToken());
        $this->assertEquals(true, $Auth->isAuthenticated());
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\AuthController');
        $method = $Class->getMethod('setToken');
        $method->setAccessible(true);
        $this->assertEquals($Auth, $method->invoke($Auth, 'test'));
        $this->assertEquals('test', $Auth->getToken());
        $this->assertEquals(true, $Auth->isAuthenticated());
        $method = $Class->getMethod('clearToken');
        $method->setAccessible(true);
        $this->assertEquals($Auth, $method->invoke($Auth));
        $this->assertEquals(null, $Auth->getToken());
        $this->assertEmpty($Auth->getToken());
        $this->assertEquals(false, $Auth->isAuthenticated());
        unset($Auth);
        $Auth = new AuthController();
        $this->assertEquals('12345', $Auth->getToken());
        $this->assertEquals(true, $Auth->isAuthenticated());
        return $Auth;
    }

    /**
     * @depends testGetToken
     * @param AuthController $Auth
     * @covers ::setActions
     * @covers ::getActions
     * @covers ::getActionEndpoint
     * @covers ::setActionEndpoint
     * @return AuthController
     */
    public function testSetActions(AuthController $Auth): AuthController {
        $this->assertEquals($this->authActions, $Auth->getActions());
        $this->assertEquals($Auth, $Auth->setActions(array()));
        $this->assertEquals(array(), $Auth->getActions());
        unset($Auth);
        $Auth = new AuthController();
        $this->assertEquals($this->authActions, $Auth->getActions());
        $AuthEndpoint = new AuthEndpoint();
        $this->assertEquals($Auth, $Auth->setActionEndpoint(AbstractAuthController::ACTION_AUTH, $AuthEndpoint));
        $this->assertEquals($AuthEndpoint, $Auth->getActionEndpoint('authenticate'));
        $LogoutEndpoint = new LogoutEndpoint();
        $this->assertEquals($Auth, $Auth->setActionEndpoint(AbstractAuthController::ACTION_LOGOUT, $LogoutEndpoint));
        $this->assertEquals($LogoutEndpoint, $Auth->getActionEndpoint('logout'));
        // ttuemer
        try {
            $Auth->getActionEndpoint('test');
        } catch (InvalidAuthenticationAction $e) {
            $this->assertEquals($e->getMessage(), "Unknown Auth Action [test] requested on Controller: MRussell\REST\Auth\Abstracts\AbstractAuthController");
        }
        return $Auth;
    }

    /**
     * @depends testSetActions
     * @param AuthController $Auth
     * @covers ::setStorageController
     * @covers ::getStorageController
     * @return AuthController
     */
    public function testSetStorageController(AuthController $Auth) {
        $Storage = new StaticStorage();
        $this->assertEquals($Auth, $Auth->setStorageController($Storage));
        $this->assertEquals($Storage, $Auth->getStorageController());
        return $Auth;
    }

    /**
     * @depends testSetStorageController
     * @param AuthController $Auth
     * @covers ::storeToken
     * @covers ::getStoredToken
     * @covers ::removeStoredToken
     */
    public function testTokenStorage(AuthController $Auth) {
        $token1 = $Auth->getToken();
        $this->assertEquals(true, $Auth->storeToken('auth_token', $token1));
        $this->assertEquals($token1, $Auth->getStoredToken('auth_token'));
        $token2 = 'abcdefg';
        $this->assertEquals(true, $Auth->storeToken('auth_token2', $token2));
        $this->assertEquals($token2, $Auth->getStoredToken('auth_token2'));
        $this->assertEquals($token1, $Auth->getStoredToken('auth_token'));
        $this->assertEquals(true, $Auth->storeToken('auth_token', $token2));
        $this->assertEquals($token2, $Auth->getStoredToken('auth_token'));
        $this->assertEquals(true, $Auth->removeStoredToken('auth_token2'));
        unset($Auth);

        $Auth = new AuthController();
        $this->assertEquals(false, $Auth->storeToken('auth_token', $token1));
        $this->assertEquals(null, $Auth->getStoredToken('auth_token'));
        $this->assertEquals(false, $Auth->removeStoredToken('auth_token'));
    }

    /**
     * @covers ::configureEndpoint
     * @covers ::configureAuthenticationEndpoint
     * @covers ::configureLogoutEndpoint
     * @return AuthController
     */
    public function testConfigureData(): AuthController {
        $Auth = new AuthController();
        $Auth->setCredentials($this->credentials);
        $AuthEndpoint = new AuthEndpoint();
        $AuthEndpoint->setBaseUrl('localhost');
        $LogoutEndpoint = new LogoutEndpoint();
        $LogoutEndpoint->setBaseUrl('localhost');
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\AuthController');
        $method = $Class->getMethod('configureEndpoint');
        $method->setAccessible(true);
        $this->assertEquals($AuthEndpoint, $method->invoke($Auth, $AuthEndpoint, AbstractAuthController::ACTION_AUTH));
        $this->assertEquals($this->credentials, $AuthEndpoint->getData()->toArray());
        $this->assertEquals($LogoutEndpoint, $method->invoke($Auth, $LogoutEndpoint, AbstractAuthController::ACTION_LOGOUT));
        $this->assertEquals(array(), $LogoutEndpoint->getData());

        return $Auth;
    }

    /**
     * @param AuthController $Auth
     * @depends testConfigureData
     * @covers ::authenticate
     * @covers ::reset
     */
    public function testAuthenticate(AuthController $Auth): AuthController {
        $Endpoint = new AuthEndpoint();
        self::$client->mockResponses->append(new Response(404));
        $Endpoint->setHttpClient(self::$client->getHttpClient());
        $Auth->setActionEndpoint(AbstractAuthController::ACTION_AUTH, $Endpoint);
        $this->assertEquals(false, $Auth->authenticate());
        self::$client->mockResponses->append(new Response(200,[],"12345"));
        $this->assertEquals(true, $Auth->authenticate());
        $this->assertEquals("12345", $Auth->getToken());
        $this->assertEquals($Auth,$Auth->reset());
        $this->assertEmpty($Auth->getToken());
        $this->assertEmpty($Auth->getCredentials());
        return $Auth;
    }

    /**
     * @param AuthController $Auth
     * @depends testConfigureData
     * @covers ::logout
     */
    public function testLogout(AuthController $Auth): AuthController {
        $Endpoint = new LogoutEndpoint();
        $Logger = new TestLogger();
        self::$client->mockResponses->append(new Response(200));
        $Endpoint->setHttpClient(self::$client->getHttpClient());
        $Auth->setLogger($Logger);
        $Auth->setActionEndpoint(AbstractAuthController::ACTION_LOGOUT, $Endpoint);
        $this->assertEquals(true, $Auth->logout());
        self::$client->mockResponses->append(new Response(404));
        $this->assertEquals(false, $Auth->logout());
        $this->assertEquals(true,$Logger->hasErrorThatContains("[REST] Logout Exception"));
        return $Auth;
    }

    /**
     * @return void
     */
    public function testNoLogoutAction()
    {
        $Auth = new AuthController();
        $Logger = new TestLogger();
        $Auth->setLogger($Logger);
        $this->assertEquals(false,$Auth->logout());
        $this->assertEquals(true,$Logger->hasDebugThatContains("Unknown Auth Action [logout] requested on Controller"));
    }
}
