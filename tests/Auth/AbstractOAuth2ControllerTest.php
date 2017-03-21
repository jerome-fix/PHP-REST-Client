<?php

namespace MRussell\REST\Tests\Auth;

use MRussell\REST\Tests\Stubs\Auth\OAuth2Controller;


/**
 * Class AbstractOAuth2ControllerTest
 * @package MRussell\REST\Tests\Auth
 * @coversDefaultClass MRussell\REST\Auth\Abstracts\AbstractOAuth2Controller
 * @group AbstractOAuth2ControllerTest
 * @group Auth
 */
class AbstractOAuth2ControllerTest extends \PHPUnit_Framework_TestCase
{


    public static function setUpBeforeClass()
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass()
    {
        //Add Tear Down for static properties here
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers ::oauthHeader
     */
    public function testOAuthHeader(){
        $this->assertEquals('Authorization',OAuth2Controller::oauthHeader());
        $this->assertEquals('Test',OAuth2Controller::oauthHeader('Test'));
        $this->assertEquals('Test',OAuth2Controller::oauthHeader());
        $Auth = new OAuth2Controller();
        $this->assertEquals('Test',$Auth->oauthHeader());
        $this->assertEquals('Authorization',$Auth->oauthHeader('Authorization'));
        $this->assertEquals('Authorization',$Auth->oauthHeader());
        $this->assertEquals('Authorization',OAuth2Controller::oauthHeader());
    }

    /**
     * @covers ::setToken
     * @covers ::configureToken
     * @covers ::isAuthenticated
     * @covers ::isTokenExpired
     */
    public function testSetToken(){

    }

    /**
     * @depends testSetToken
     * @covers ::configure
     */
    public function testConfigure(){

    }

}
