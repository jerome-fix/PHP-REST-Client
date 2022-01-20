<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\JSON;
use MRussell\Http\Response\Standard;
use MRussell\REST\Tests\Stubs\Auth\AuthController;
use MRussell\REST\Tests\Stubs\Endpoint\BasicEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\EndpointData;

/**
 * Class AbstractEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractEndpoint
 * @group AbstractEndpointTest
 */
class AbstractEndpointTest extends TestCase
{

    public static function setUpBeforeClass()
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass()
    {
        //Add Tear Down for static properties here
    }

    protected $options = array(
        'foo',
        'bar'
    );

    protected $properties = array(
        'url' => '$foo/$bar/$:test'
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
     * @covers ::setOptions
     * @covers ::setProperty
     * @covers ::getOptions
     * @covers ::getProperties
     * @covers ::getData
     * @covers ::getRequest
     * @covers ::getResponse
     * @covers ::getAuth
     * @covers ::getBaseUrl
     * @covers ::getEndpointUrl
     */
    public function testConstructor()
    {
        $Endpoint = new BasicEndpoint();
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => false
        ), $Endpoint->getProperties());
        $this->assertEquals(array(), $Endpoint->getUrlArgs());
        $this->assertEmpty($Endpoint->getData());
        $this->assertEmpty($Endpoint->getRequest());
        $this->assertEmpty($Endpoint->getResponse());
        $this->assertEmpty($Endpoint->getAuth());
        $this->assertEmpty($Endpoint->getBaseUrl());
        $this->assertEquals('', $Endpoint->getEndPointUrl());

        $Endpoint = new BasicEndpoint($this->options);
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => false
        ), $Endpoint->getProperties());
        $this->assertEquals($this->options, $Endpoint->getUrlArgs());
        $this->assertEmpty($Endpoint->getData());
        $this->assertEmpty($Endpoint->getRequest());
        $this->assertEmpty($Endpoint->getResponse());
        $this->assertEmpty($Endpoint->getAuth());
        $this->assertEmpty($Endpoint->getBaseUrl());
        $this->assertEquals('', $Endpoint->getEndPointUrl());

        $Endpoint = new BasicEndpoint($this->options, $this->properties);
        $this->assertEquals(array(
            'url' => '$foo/$bar/$:test',
            'httpMethod' => '',
            'auth' => false
        ), $Endpoint->getProperties());
        $this->assertEquals($this->options, $Endpoint->getUrlArgs());
        $this->assertEmpty($Endpoint->getData());
        $this->assertEmpty($Endpoint->getRequest());
        $this->assertEmpty($Endpoint->getResponse());
        $this->assertEmpty($Endpoint->getAuth());
        $this->assertEmpty($Endpoint->getBaseUrl());
        $this->assertEquals('$foo/$bar/$:test', $Endpoint->getEndPointUrl());
    }

    /**
     * @covers ::setOptions
     * @covers ::getOptions
     */
    public function testSetOptions()
    {
        $Endpoint = new BasicEndpoint();
        $this->assertEquals(array(), $Endpoint->getUrlArgs());
        $this->assertEquals($Endpoint, $Endpoint->setUrlArgs($this->options));
        $this->assertEquals($this->options, $Endpoint->getUrlArgs());
        $this->assertEquals($Endpoint, $Endpoint->setUrlArgs(array()));
        $this->assertEquals(array(), $Endpoint->getUrlArgs());
    }

    /**
     * @covers ::setProperties
     * @covers ::getProperties
     * @covers ::setProperty
     */
    public function testSetProperties()
    {
        $Endpoint = new BasicEndpoint();
        $this->assertEquals($Endpoint,$Endpoint->setProperties(array()));
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => ''
        ),$Endpoint->getProperties());
        $this->assertEquals($Endpoint,$Endpoint->setProperties($this->properties));
        $props = $this->properties;
        $props['httpMethod'] = '';
        $props['auth'] = FALSE;
        $this->assertEquals($props,$Endpoint->getProperties());
        $this->assertEquals($Endpoint,$Endpoint->setProperty(BasicEndpoint::PROPERTY_AUTH,TRUE));
        $props['auth'] = TRUE;
        $this->assertEquals($props,$Endpoint->getProperties());
    }

    /**
     * @depends testSetProperties
     * @covers ::setBaseUrl
     * @covers ::getBaseUrl
     * @covers ::getEndpointUrl
     */
    public function testSetBaseUrl(){
        $Endpoint = new BasicEndpoint();
        $this->assertEquals($Endpoint,$Endpoint->setProperties($this->properties));
        $this->assertEquals($Endpoint,$Endpoint->setBaseUrl('localhost'));
        $this->assertEquals('localhost',$Endpoint->getBaseUrl());
        $this->assertEquals('localhost/$foo/$bar/$:test',$Endpoint->getEndPointUrl(TRUE));
        $this->assertEquals($Endpoint,$Endpoint->setBaseUrl(NULL));
        $this->assertEquals(NULL,$Endpoint->getBaseUrl());
    }

    /**
     * @covers ::setData
     * @covers ::getData
     */
    public function testSetData(){
        $Endpoint = new BasicEndpoint();
        $this->assertEquals($Endpoint,$Endpoint->setData('test'));
        $this->assertEquals('test',$Endpoint->getData());
        $this->assertEquals($Endpoint,$Endpoint->setData(NULL));
        $this->assertEquals(NULL,$Endpoint->getData());
        $this->assertEquals($Endpoint,$Endpoint->setData(array()));
        $this->assertEquals(array(),$Endpoint->getData());
        $data = new EndpointData();
        $this->assertEquals($Endpoint,$Endpoint->setData($data));
        $this->assertEquals($data,$Endpoint->getData());
    }

    /**
     * @covers ::setRequest
     * @covers ::getRequest
     */
    public function testSetRequest(){
        $Endpoint = new BasicEndpoint();
        $Request = new Curl();
        $this->assertEquals($Endpoint,$Endpoint->setRequest($Request));
        $this->assertEquals($Request,$Endpoint->getRequest());
    }

    /**
     * @covers ::setResponse
     * @covers ::getResponse
     */
    public function testSetResponse(){
        $Endpoint = new BasicEndpoint();
        $Response = new Standard();
        $this->assertEquals($Endpoint,$Endpoint->setResponse($Response));
        $this->assertEquals($Response,$Endpoint->getResponse());
    }

    /**
     * @covers ::setAuth
     * @covers ::getAuth
     */
    public function testSetAuth(){
        $Endpoint = new BasicEndpoint();
        $Auth = new AuthController();
        $this->assertEquals($Endpoint,$Endpoint->setAuth($Auth));
        $this->assertEquals($Auth,$Endpoint->getAuth());
    }

    /**
     * @depends testSetProperties
     * @covers ::authRequired
     */
    public function testAuthRequired(){
        $Endpoint = new BasicEndpoint();
        $this->assertEquals(FALSE,$Endpoint->authRequired());
        $this->assertEquals($Endpoint,$Endpoint->setProperty('auth',TRUE));
        $this->assertEquals(TRUE,$Endpoint->authRequired());
    }

    /**
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidRequest
     * @covers ::execute
     */
    public function testInvalidRequest(){
        $Endpoint = new BasicEndpoint();
        $Endpoint->execute();
    }

    /**
     * @covers ::execute
     * @covers ::configureRequest
     * @covers ::configureResponse
     * @covers ::configureData
     * @covers ::verifyUrl
     */
    public function testExecute(){
        $Endpoint = new BasicEndpoint();
        $Request = new Curl();
        $this->assertEquals($Endpoint,$Endpoint->setBaseUrl('http://localhost'));
        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','basic'));
        $this->assertEquals($Endpoint,$Endpoint->setRequest($Request));
        $this->assertEquals($Endpoint,$Endpoint->execute());
        $this->assertEquals('http://localhost/basic',$Request->getURL());

        $Response = new Standard();
        $this->assertEquals($Endpoint,$Endpoint->setResponse($Response));
        $this->assertEquals($Endpoint,$Endpoint->execute());
        $this->assertNotEmpty($Response->getRequest());

        $this->assertEquals($Endpoint,$Endpoint->setProperty('httpMethod','POST'));
        $this->assertEquals($Endpoint,$Endpoint->setProperty('auth',TRUE));
        $Auth = new AuthController();
        $this->assertEquals($Endpoint,$Endpoint->setAuth($Auth));
        $this->assertEquals($Endpoint,$Endpoint->execute());
        unset($Endpoint);
    }

    /**
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidUrl
     */
    public function testInvalidUrl(){
        $Endpoint = new BasicEndpoint();
        $Request = new Curl();
        $this->assertEquals($Endpoint,$Endpoint->setBaseUrl('http://localhost'));
        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo'));
        $this->assertEquals('$foo',$Endpoint->getEndPointUrl());
        $this->assertEquals(array(),$Endpoint->getUrlArgs());
        $this->assertEquals($Endpoint,$Endpoint->setRequest($Request));
        $Endpoint->execute();
    }

    /**
     * @covers ::configureUrl
     *
     */
    public function testConfigureUrl(){
        $Endpoint = new BasicEndpoint();
        $Class = new \ReflectionClass('MRussell\REST\Tests\Stubs\Endpoint\BasicEndpoint');
        $method = $Class->getMethod('configureURL');
        $method->setAccessible(TRUE);
        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo'));
        $this->assertEquals('bar',$method->invoke($Endpoint,array('bar')));

        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo/$bar'));
        $this->assertEquals('bar/foo',$method->invoke($Endpoint,array('bar','foo')));

        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo/$bar/$:baz'));
        $this->assertEquals('bar/foo',$method->invoke($Endpoint,array('bar','foo')));

        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo/$bar/$:baz'));
        $this->assertEquals('bar/foo/1234',$method->invoke($Endpoint,array(
                'foo' => 'bar',
                0 => 'foo',
                1 => 1234
            )
        ));
        $this->assertEquals('bar/foo/1234',$method->invoke($Endpoint,array(
                'foo' => 'bar',
                3 => 'foo',
                4 => 1234
            )
        ));

        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo/$bar/$:baz/$:foz'));
        $this->assertEquals('bar/foo/foz/1234',$method->invoke($Endpoint,array(
                'foo' => 'bar',
                'bar' => 'foo',
                'baz' => 'foz',
                0 => 1234
            )
        ));

        $this->assertEquals($Endpoint,$Endpoint->setProperty('url','$foo/$bar/$:baz/$:foz/$:aaa/$:bbb'));
        $this->assertEquals('bar/foo/foz/1234',$method->invoke($Endpoint,array(
                'foo' => 'bar',
                'bar' => 'foo',
                'baz' => 'foz',
                0 => 1234
            )
        ));
    }

}
