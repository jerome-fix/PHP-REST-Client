<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\JSON\Endpoint;


/**
 * Class JSONEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\JSON\Endpoint
 * @group JSONEndpointTest
 */
class JSONEndpointTest extends TestCase
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
     * @covers ::__construct
     */
    public function testConstructor(){
        $Endpoint = new Endpoint();
        $Request = new JSON();
        $Response = new \MRussell\Http\Response\JSON();
        $this->assertNotEmpty($Endpoint->getRequest());
        $this->assertNotEmpty($Endpoint->getResponse());
        $this->assertEquals($Request,$Endpoint->getRequest());
        $this->assertEquals($Response,$Endpoint->getResponse());
    }
}
