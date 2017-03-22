<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\JSON;
use MRussell\REST\Endpoint\Provider\EndpointProviderInterface;
use MRussell\REST\Tests\Stubs\Endpoint\AuthEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\EndpointProvider;
use MRussell\REST\Tests\Stubs\Endpoint\EndpointProviderWithDefaults;


/**
 * Class AbstractEndpointProviderTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Provider\AbstractEndpointProvider
 * @group AbstractEndpointProviderTest
 */
class AbstractEndpointProviderTest extends \PHPUnit_Framework_TestCase
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
     * @covers ::registerEndpoint
     */
    public function testConstructor(){
        $Provider = new EndpointProvider();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Endpoint\EndpointProvider');
        $property = $Class->getProperty('registry');
        $property->setAccessible(TRUE);
        $this->assertEquals(array(),$property->getValue($Provider));

        $Provider = new EndpointProviderWithDefaults();
        $this->assertNotEmpty($property->getValue($Provider));
    }

    /**
     * @covers ::registerEndpoint
     * @return EndpointProviderInterface
     */
    public function testRegisterEndpoint(){
        $Provider = new EndpointProvider();
        $this->assertEquals($Provider,$Provider->registerEndpoint('auth','MRussell\REST\Tests\Stubs\Endpoint\AuthEndpoint'));
        $this->assertEquals($Provider,$Provider->registerEndpoint('foo','MRussell\REST\Endpoint\JSON\Endpoint',array(
            'url' => 'foo',
            'httpMethod' => JSON::HTTP_GET
        )));
        return $Provider;
    }

    /**
     * @depends testRegisterEndpoint
     * @param EndpointProviderInterface $Provider
     * @covers ::registerEndpoint
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidRegistration
     */
    public function testInvalidRegistration(EndpointProviderInterface $Provider){
        $Provider->registerEndpoint("baz","baz");
    }

    /**
     * @depends testRegisterEndpoint
     * @covers ::hasEndpoint
     * @covers ::getEndpoint
     * @covers ::buildEndpoint
     * @param EndpointProviderInterface $Provider
     */
    public function testGetEndpoint(EndpointProviderInterface $Provider){
        $this->assertEquals(FALSE, $Provider->hasEndpoint('test'));
        $this->assertEquals(TRUE, $Provider->hasEndpoint('foo'));
        $this->assertEquals(TRUE, $Provider->hasEndpoint('auth'));
        $Auth = new AuthEndpoint();
        $this->assertEquals($Auth, $Provider->getEndpoint('auth'));
        $FooEP = $Provider->getEndpoint('foo');
        $this->assertNotEmpty($FooEP);
        $this->assertEquals('foo',$FooEP->getEndPointUrl());
        $this->assertEquals(array(
            'url' => 'foo',
            'httpMethod' => JSON::HTTP_GET,
            'auth' => FALSE
        ),$FooEP->getProperties());
    }

    /**
     * @depends testRegisterEndpoint
     * @param EndpointProviderInterface $Provider
     * @covers ::getEndpoint
     * @expectedException MRussell\REST\Exception\Endpoint\UnknownEndpoint
     */
    public function testUnknownEndpoint(EndpointProviderInterface $Provider){
        $Provider->getEndpoint('test');
    }

}
