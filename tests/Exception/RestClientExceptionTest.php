<?php

namespace MRussell\REST\Tests\Exception;

use MRussell\REST\Exception\Endpoint\EndpointException;
use MRussell\REST\Exception\RestClientException;


/**
 * Class RestClientExceptionTest
 * @package MRussell\REST\Tests\Exception
 * @coversDefaultClass MRussell\REST\Exception\RestClientException
 * @group RestClientExceptionTest
 */
class UnknownExceptionTest extends TestCase
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
        $Exception = new RestClientException();
        $this->assertEquals('An Unknown Exception occurred in the REST Client Framework',$Exception->getMessage());
        $Exception = new EndpointException(array('AuthEndpoint'));
        $this->assertEquals('Unknown Exception occurred on Endpoint: AuthEndpoint',$Exception->getMessage());
    }
}
