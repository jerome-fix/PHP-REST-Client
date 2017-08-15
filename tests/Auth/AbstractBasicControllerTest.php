<?php
/**
 * User: mrussell
 * Date: 8/15/17
 * Time: 8:50 AM
 */

namespace MRussell\REST\Tests\Auth;

use MRussell\Http\Request\JSON;
use MRussell\REST\Tests\Stubs\Auth\BasicController;


/**
 * Class AbstractBasicControllerTest
 * @package MRussell\REST\Tests\Auth
 * @coversDefaultClass MRussell\REST\Auth\Abstracts\AbstractBasicController
 * @group AbstractBasicControllerTest
 */
class AbstractBasicControllerTest extends \PHPUnit_Framework_TestCase
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
     * @covers ::configureRequest
     * @covers ::getAuthHeaderValue
     */
    public function testConfigureRequest()
    {
        $Auth = new BasicController();
        $Request = new JSON();
        $this->assertEquals($Auth,$Auth->configureRequest($Request));
        $headers = $Request->getHeaders();
        $this->assertEquals("Basic ",$headers['Authorization']);
        $Auth->setCredentials(array(
            'username' => 'foo',
            'password' => 'bar'
        ));
        $this->assertEquals($Auth,$Auth->configureRequest($Request));
        $headers = $Request->getHeaders();
        $this->assertEquals('Basic '.base64_encode("foo:bar"),$headers['Authorization']);
    }

}
