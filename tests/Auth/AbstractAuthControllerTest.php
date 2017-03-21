<?php
/**
 * Created by PhpStorm.
 * User: mrussell
 * Date: 3/21/17
 * Time: 11:38 AM
 */

namespace MRussell\REST\Tests\Auth;

use MRussell\REST\Storage\StaticStorage;
use MRussell\REST\Tests\Stubs\Auth\AuthController;
use MRussell\REST\Tests\Stubs\Endpoint\AuthEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\LogoutEndpoint;

/**
 * Class AbstractAuthControllerTest
 * @package MRussell\REST\Tests\Auth\
 * @coversDefaultClass MRussell\REST\Auth\Abstracts\AbstractAuthController
 * @group AbstractAuthControllerTest
 * @group Auth
 */
class AbstractAuthControllerTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass()
    {
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

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::getActions
     * @return AuthController
     */
    public function testConstructor(){
        $Auth = new AuthController();
        $this->assertEquals($this->authActions,$Auth->getActions());
        $actions = $this->authActions;
        $actions[] = 'test';
        $this->assertEquals($Auth,$Auth->setActions($actions));
        $this->assertEquals($actions,$Auth->getActions());
        unset($Auth);

        $Auth = new AuthController();
        $this->assertEquals($this->authActions,$Auth->getActions());
        return $Auth;
    }

    /**
     * @depends testConstructor
     * @param AuthController $Auth
     * @covers ::setCredentials
     * @covers ::getCredentials
     * @return AuthController
     */
    public function testSetCredentials(AuthController $Auth){
        $this->assertEquals($Auth,$Auth->setCredentials($this->credentials));
        $this->assertEquals($this->credentials,$Auth->getCredentials());
        $Auth->setCredentials(array());
        $this->assertEquals(array(),$Auth->getCredentials());
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
    public function testGetToken(AuthController $Auth){
        $this->assertEquals('12345',$Auth->getToken());
        $this->assertEquals(TRUE,$Auth->isAuthenticated());
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\AuthController');
        $method = $Class->getMethod('setToken');
        $method->setAccessible(TRUE);
        $this->assertEquals($Auth,$method->invoke($Auth, 'test'));
        $this->assertEquals('test',$Auth->getToken());
        $this->assertEquals(TRUE,$Auth->isAuthenticated());
        $method = $Class->getMethod('clearToken');
        $method->setAccessible(TRUE);
        $this->assertEquals($Auth,$method->invoke($Auth));
        $this->assertEquals(NULL,$Auth->getToken());
        $this->assertEmpty($Auth->getToken());
        $this->assertEquals(FALSE,$Auth->isAuthenticated());
        unset($Auth);
        $Auth = new AuthController();
        $this->assertEquals('12345',$Auth->getToken());
        $this->assertEquals(TRUE,$Auth->isAuthenticated());
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
    public function testSetActions(AuthController $Auth){
        $this->assertEquals($this->authActions,$Auth->getActions());
        $this->assertEquals($Auth,$Auth->setActions(array()));
        $this->assertEquals(array(),$Auth->getActions());
        unset($Auth);
        $Auth = new AuthController();
        $this->assertEquals($this->authActions,$Auth->getActions());
        $AuthEndpoint = new AuthEndpoint();
        $this->assertEquals($Auth,$Auth->setActionEndpoint(AuthController::ACTION_AUTH,$AuthEndpoint));
        $this->assertEquals($AuthEndpoint,$Auth->getActionEndpoint('authenticate'));
        $LogoutEndpoint = new LogoutEndpoint();
        $this->assertEquals($Auth,$Auth->setActionEndpoint(AuthController::ACTION_LOGOUT,$LogoutEndpoint));
        $this->assertEquals($LogoutEndpoint,$Auth->getActionEndpoint('logout'));
        $this->assertEquals(NULL,$Auth->getActionEndpoint('test'));
        $this->assertEmpty($Auth->getActionEndpoint('test'));
        return $Auth;
    }

    /**
     * @depends testSetActions
     * @param AuthController $Auth
     * @covers ::setStorageController
     * @covers ::getStorageController
     * @return AuthController
     */
    public function testSetStorageController(AuthController $Auth){
        $Storage = new StaticStorage();
        $this->assertEquals($Auth,$Auth->setStorageController($Storage));
        $this->assertEquals($Storage,$Auth->getStorageController());
        return $Auth;
    }

    /**
     * @depends testSetStorageController
     * @param AuthController $Auth
     * @covers ::storeToken
     * @covers ::getStoredToken
     * @covers ::removeStoredToken
     */
    public function testTokenStorage(AuthController $Auth){
        $token1 = $Auth->getToken();
        $this->assertEquals(TRUE,$Auth->storeToken('auth_token',$token1));
        $this->assertEquals($token1,$Auth->getStoredToken('auth_token'));
        $token2 = 'abcdefg';
        $this->assertEquals(TRUE,$Auth->storeToken('auth_token2',$token2));
        $this->assertEquals($token2,$Auth->getStoredToken('auth_token2'));
        $this->assertEquals($token1,$Auth->getStoredToken('auth_token'));
        $this->assertEquals(TRUE,$Auth->storeToken('auth_token',$token2));
        $this->assertEquals($token2,$Auth->getStoredToken('auth_token'));
        $this->assertEquals(TRUE,$Auth->removeStoredToken('auth_token2'));
        unset($Auth);

        $Auth = new AuthController();
        $this->assertEquals(FALSE,$Auth->storeToken('auth_token',$token1));
        $this->assertEquals(NULL,$Auth->getStoredToken('auth_token'));
        $this->assertEquals(FALSE,$Auth->removeStoredToken('auth_token'));
    }

    /**
     * @covers ::configureEndpoint
     * @covers ::configureAuthenticationEndpoint
     * @covers ::configureLogoutEndpoint
     * @return AuthController
     */
    public function testConfigureData(){
        $Auth = new AuthController();
        $Auth->setCredentials($this->credentials);
        $AuthEndpoint = new AuthEndpoint();
        $AuthEndpoint->setBaseUrl('localhost');
        $this->assertEquals($Auth,$Auth->setActionEndpoint(AuthController::ACTION_AUTH,$AuthEndpoint));
        $LogoutEndpoint = new LogoutEndpoint();
        $LogoutEndpoint->setBaseUrl('localhost');
        $this->assertEquals($Auth,$Auth->setActionEndpoint(AuthController::ACTION_LOGOUT,$LogoutEndpoint));
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Auth\AuthController');
        $method = $Class->getMethod('configureEndpoint');
        $method->setAccessible(TRUE);
        $this->assertEquals(FALSE,$method->invoke($Auth,'foo'));
        $this->assertEquals($AuthEndpoint,$method->invoke($Auth,AuthController::ACTION_AUTH));
        $this->assertEquals($this->credentials,$AuthEndpoint->getData());
        $this->assertEquals($LogoutEndpoint,$method->invoke($Auth,AuthController::ACTION_LOGOUT));
        $this->assertEquals(array(),$LogoutEndpoint->getData());

        return $Auth;
    }

    /**
     * @param AuthController $Auth
     * @depends testConfigureData
     * @covers ::authenticate
     */
    public function testAuthenticate(AuthController $Auth){
        $this->assertEquals(FALSE,$Auth->authenticate());
    }

    /**
     * @param AuthController $Auth
     * @depends testConfigureData
     * @covers ::logout
     */
    public function testLogout(AuthController $Auth){
        $this->assertEquals(FALSE,$Auth->logout());
    }
}
