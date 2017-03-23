<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\REST\Endpoint\JSON\ModelEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\CollectionEndpointWithModel;
use MRussell\REST\Tests\Stubs\Endpoint\CollectionEndpoint;


/**
 * Class AbstractCollectionEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractCollectionEndpoint
 * @group AbstractCollectionEndpointTest
 */
class AbstractCollectionEndpointTest extends \PHPUnit_Framework_TestCase
{
    protected static $_REFLECTED_CLASS = 'MRussell\REST\Tests\Stubs\Endpoint\CollectionEndpoint';

    protected $collection = array(
        'abc123' => array(
            'id' => 'abc123',
            'name' => 'foo',
            'foo' => 'bar'
        ),
        'efg234' => array(
            'id' => 'efg234',
            'name' => 'test',
            'foo' => ''
        )
    );

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
     * @covers ::setModelEndpoint
     */
    public function testConstructor(){
        $Endpoint = new CollectionEndpoint();
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS);
        $model = $Class->getProperty('model');
        $model->setAccessible(TRUE);
        $this->assertEmpty($model->getValue($Endpoint));

        $Endpoint = new CollectionEndpointWithModel();
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS.'WithModel');
        $model = $Class->getProperty('model');
        $model->setAccessible(TRUE);
        $this->assertEquals('MRussell\\REST\\Tests\\Stubs\\Endpoint\\ModelEndpoint',$model->getValue($Endpoint));
    }

    /**
     * @covers ::offsetSet
     * @covers ::offsetExists
     * @covers ::offsetUnset
     * @covers ::offsetGet
     * @covers ::update
     * @covers ::asArray
     * @covers ::get
     * @covers ::buildModel
     * @covers ::clear
     * @covers ::reset
     */
    public function testDataAccess(){
        $Collection = new CollectionEndpointWithModel();
        $Collection[] = array(
            'foo' => 'bar',
            'abc' => 123
        );
        $this->assertEquals(array(array(
                                      'foo' => 'bar',
                                      'abc' => 123
                                  )),$Collection->asArray());
        $this->assertEquals(array(
                              'foo' => 'bar',
                              'abc' => 123
                          ),$Collection[0]);
        $this->assertEquals(TRUE,isset($Collection[0]));
        unset($Collection[0]);
        $this->assertEquals(FALSE,isset($Collection[0]));
        $this->assertEquals(array(),$Collection->asArray());
        $this->assertEquals($Collection,$Collection->update($this->collection));
        $this->assertEquals($this->collection,$Collection->asArray());
        $this->assertEquals(array(
            'id' => 'abc123',
            'name' => 'foo',
            'foo' => 'bar'
        ),$Collection['abc123']);
        $Collection['k2r2d2'] = array(
            'id' => 'k2r2d2',
            'name' => 'Rogue One',
            'foo' => 'bar'
        );
        $this->assertEquals(array(
            'id' => 'k2r2d2',
            'name' => 'Rogue One',
            'foo' => 'bar'
        ),$Collection['k2r2d2']);
        $Model = $Collection->get('abc123');
        $this->assertEquals(TRUE,is_object($Model));
        $this->assertEquals('bar',$Model->get('foo'));
        $this->assertEquals($Collection,$Collection->reset());
        $this->assertEquals(array(),$Collection->asArray());
        $this->assertEquals($Collection,$Collection->update($this->collection));
        $this->assertEquals($this->collection,$Collection->asArray());
        $this->assertEquals($Collection,$Collection->reset());
        $this->assertEquals(array(),$Collection->asArray());

        $Collection = new CollectionEndpoint();
        $this->assertEquals($Collection,$Collection->update($this->collection));
        $Model = $Collection->get('abc123');
        $this->assertEquals(TRUE,is_array($Model));
        $this->assertEquals(array(
            'id' => 'abc123',
            'name' => 'foo',
            'foo' => 'bar'
        ),$Model);
    }

    /**
     * @covers ::setModelEndpoint
     */
    public function testSetModelEndpoint(){
        $Collection = new CollectionEndpointWithModel();
        $Collection->setModelEndpoint(new ModelEndpoint());
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS);
        $model = $Class->getProperty('model');
        $model->setAccessible(TRUE);
        $this->assertEquals('MRussell\\REST\\Endpoint\\JSON\\ModelEndpoint',$model->getValue($Collection));
        $Collection->setModelEndpoint('MRussell\\REST\\Tests\\Stubs\\Endpoint\\ModelEndpoint');
        $this->assertEquals('MRussell\\REST\\Tests\\Stubs\\Endpoint\\ModelEndpoint',$model->getValue($Collection));
    }

    /**
     * @depends testSetModelEndpoint
     * @covers ::setModelEndpoint
     * @expectedException MRussell\REST\Exception\Endpoint\UnknownEndpoint
     */
    public function testUnknownEndpoint(){
        $Collection = new CollectionEndpointWithModel();
        $Collection->setModelEndpoint('test');
    }

    /**
     * @covers ::getEndpointUrl
     */
    public function testGetEndpointUrl(){
        $Collection = new CollectionEndpointWithModel();
        $this->assertEquals("account",$Collection->getEndPointUrl());
        $this->assertEquals($Collection,$Collection->setProperty('url','accounts'));
        $this->assertEquals("accounts",$Collection->getEndPointUrl());
        $this->assertEquals($Collection,$Collection->setBaseUrl('localhost'));
        $this->assertEquals("localhost/accounts",$Collection->getEndPointUrl(TRUE));
        $this->assertEquals($Collection,$Collection->setProperty('url',''));
        $this->assertEquals("localhost/account",$Collection->getEndPointUrl(TRUE));
    }

    /**
     * @covers ::fetch
     */
    public function testFetch(){
        $Collection = new \MRussell\REST\Endpoint\JSON\CollectionEndpoint();
        $Collection->setBaseUrl('localhost');
        $Collection->setProperty('url','foo');
        $Collection->fetch();
        $props = $Collection->getProperties();
        $this->assertEquals('GET',$props['httpMethod']);
    }

    /**
     * @covers ::configureResponse
     * @covers ::updateCollection
     */
    public function testConfigureResponse(){
        $Collection = new \MRussell\REST\Endpoint\JSON\CollectionEndpoint();
        $Collection->setBaseUrl('localhost');
        $Collection->setProperty('url','foo');
        $Response = $Collection->getResponse();

        $ReflectedResponse = new \ReflectionClass('MRussell\Http\Response\JSON');
        $ReflectedCollection = new \ReflectionClass('MRussell\REST\Endpoint\JSON\CollectionEndpoint');
        $status = $ReflectedResponse->getProperty('status');
        $status->setAccessible(TRUE);
        $status->setValue($Response,'200');
        $method = $ReflectedCollection->getMethod('configureResponse');
        $method->setAccessible(TRUE);
        $Collection->setResponse($Response);
        $this->assertEquals($Response,$method->invoke($Collection,$Response));
        $this->assertNotEmpty($Response->getRequest());

        $body = $ReflectedResponse->getProperty('body');
        $body->setAccessible(TRUE);
        $body->setValue($Response,json_encode(array(
            array(
                'id' => 'abc123',
                'name' => 'foo',
                'foo' => 'bar'
            ),
            array(
                'id' => 'efg234',
                'name' => 'test',
                'foo' => ''
            )
        )));
        $Collection->setResponse($Response);
        $ReflectedCollection = new \ReflectionClass(static::$_REFLECTED_CLASS."WithModel");
        $updateCollection = $ReflectedCollection->getMethod('updateCollection');
        $updateCollection->setAccessible(TRUE);
        $updateCollection->invoke($Collection);
        $this->assertEquals(array(
            array(
                'id' => 'abc123',
                'name' => 'foo',
                'foo' => 'bar'
            ),
            array(
                'id' => 'efg234',
                'name' => 'test',
                'foo' => ''
            )
        ),$Collection->asArray());

        $Collection = new CollectionEndpointWithModel();
        $Collection->setResponse($Response);
        $ReflectedCollection = new \ReflectionClass(static::$_REFLECTED_CLASS."WithModel");
        $updateCollection = $ReflectedCollection->getMethod('updateCollection');
        $updateCollection->setAccessible(TRUE);
        $updateCollection->invoke($Collection);
        $this->assertEquals($this->collection,$Collection->asArray());

        $body->setValue($Response,json_encode(array(
            array(
                'id' => 'abc123',
                'name' => 'foo',
                'foo' => 'bar'
            ),
            array(
                'id' => 'efg234',
                'name' => 'test',
                'foo' => ''
            ),
            array(
                'name' => 'no_id',
                'foo' => ''
            )
        )));
        $Collection->setResponse($Response);
        $updateCollection->invoke($Collection);
        $this->assertEquals(array(
            'abc123' => array(
                'id' => 'abc123',
                'name' => 'foo',
                'foo' => 'bar'
            ),
            'efg234' => array(
                'id' => 'efg234',
                'name' => 'test',
                'foo' => ''
            ),
            0 => array(
                'name' => 'no_id',
                'foo' => ''
            )
        ),$Collection->asArray());
    }
}
