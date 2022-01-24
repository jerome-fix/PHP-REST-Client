<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\JSON\SmartEndpoint;
use PHPUnit\Framework\TestCase;

/**
 * Class JSONEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\JSON\SmartEndpoint
 * @group JSONEndpointTest
 */
class JSONSmartEndpointTest extends TestCase
{

    public static function setUpBeforeClass():void
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass():void
    {
        //Add Tear Down for static properties here
    }

    public function setUp():void
    {
        parent::setUp();
    }

    public function tearDown():void
    {
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(){
        $Endpoint = new SmartEndpoint();
        $Request = new JSON();
        $Response = new \MRussell\Http\Response\JSON();
        $this->assertNotEmpty($Endpoint->getRequest());
        $this->assertNotEmpty($Endpoint->getResponse());
        $this->assertEquals($Request,$Endpoint->getRequest());
        $this->assertEquals($Response,$Endpoint->getResponse());
    }
}
