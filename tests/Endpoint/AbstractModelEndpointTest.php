<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\JSON;
use MRussell\REST\Exception\Endpoint\UnknownModelAction;
use MRussell\REST\Tests\Stubs\Endpoint\ModelEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\ModelEndpointWithActions;

/**
 * Class AbstractModelEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint
 * @group AbstractModelEndpointTest
 */
class AbstractModelEndpointTest extends \PHPUnit_Framework_TestCase
{
    protected static $_REFLECTED_CLASS = 'MRussell\REST\Tests\Stubs\Endpoint\ModelEndpoint';

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
        ModelEndpoint::modelIdKey('id');
        parent::tearDown();
    }

    /**
     * @covers ::modelIdKey
     */
    public function testModelIdKey(){
        $this->assertEquals('id',ModelEndpoint::modelIdKey());
        $this->assertEquals('key',ModelEndpoint::modelIdKey('key'));
        $this->assertEquals('key',ModelEndpoint::modelIdKey());
        $Model = new ModelEndpoint();
        $this->assertEquals('key',$Model->modelIdKey());
        $this->assertEquals('id',ModelEndpoint::modelIdKey('id'));
        $this->assertEquals('id',$Model->modelIdKey());
        $this->assertEquals('key',$Model->modelIdKey('key'));
        $this->assertEquals('key',ModelEndpoint::modelIdKey());
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(){
        $Model = new ModelEndpoint();
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS);
        $actions = $Class->getProperty('actions');
        $actions->setAccessible(TRUE);
        $this->assertEquals(array(
            'create' => Curl::HTTP_POST,
            'retrieve' => Curl::HTTP_GET,
            'update' => Curl::HTTP_PUT,
            'delete' => Curl::HTTP_DELETE
        ),$actions->getValue($Model));
    }

    /**
     * @covers ::__call
     * @covers ::configureAction
     */
    public function testCall(){
        $Model = new ModelEndpointWithActions();
        $Request = new JSON();
        $Model->setRequest($Request);
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS."WithActions");
        $actions = $Class->getProperty('actions');
        $actions->setAccessible(TRUE);
        $this->assertEquals(array(
            'foo' => Curl::HTTP_GET,
            'create' => Curl::HTTP_POST,
            'retrieve' => Curl::HTTP_GET,
            'update' => Curl::HTTP_PUT,
            'delete' => Curl::HTTP_DELETE
        ),$actions->getValue($Model));
        $this->assertEquals($Model,$Model->foo());
        $props = $Model->getProperties();
        $this->assertEquals(Curl::HTTP_GET,$props['httpMethod']);
    }

    /**
     * @covers ::__call
     * @expectedException MRussell\REST\Exception\Endpoint\UnknownModelAction
     */
    public function testCallException(){
        $Model = new ModelEndpointWithActions();
        $Model->bar();
    }

    /**
     * @covers ::offsetSet
     * @covers ::offsetGet
     * @covers ::offsetUnset
     * @covers ::offsetExists
     * @covers ::set
     * @covers ::get
     * @covers ::asArray
     * @covers ::reset
     * @covers ::clear
     * @covers ::update
     */
    public function testDataAccess(){
        $Model = new ModelEndpoint();
        $this->assertEquals($Model,$Model->set('foo','bar'));
        $this->assertEquals(TRUE,isset($Model['foo']));
        $this->assertEquals('bar',$Model['foo']);
        $this->assertEquals(array(
            'foo' => 'bar'
        ),$Model->asArray());
        $this->assertEquals($Model,$Model->clear());
        $this->assertEquals(array(),$Model->asArray());
        $Model['foo'] = 'bar';
        $this->assertEquals('bar',$Model->get('foo'));
        unset($Model['foo']);
        $this->assertEquals(FALSE,isset($Model['foo']));
        $this->assertEquals(array(),$Model->asArray());

        $Model[] = array(
            'foo' => 'bar'
        );
        $this->assertEquals(array(array(
                                      'foo' => 'bar'
                                  )),$Model->asArray());
        $this->assertEquals($Model,$Model->update(array(
            'foo' => 'bar'
        )));
        $this->assertEquals('bar',$Model->get('foo'));
        $this->assertEquals(array(
            'foo' => 'bar'
        ),$Model[0]);
        $this->assertEquals($Model,$Model->reset());
        $this->assertEquals(array(),$Model->asArray());
    }
}
