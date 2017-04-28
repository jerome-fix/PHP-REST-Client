<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\JSON;
use MRussell\REST\Exception\Endpoint\MissingModelId;
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

    /**
     * @covers ::setCurrentAction
     * @covers ::getCurrentAction
     */
    public function testCurrentAction(){
        $Model = new ModelEndpoint();
        $this->assertEquals($Model,$Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_CREATE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_CREATE,$Model->getCurrentAction());
        $this->assertEquals($Model,$Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_UPDATE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_UPDATE,$Model->getCurrentAction());
        $this->assertEquals($Model,$Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_DELETE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE,$Model->getCurrentAction());
        $this->assertEquals($Model,$Model->setCurrentAction('foo'));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE,$Model->getCurrentAction());
    }

    /**
     * @covers ::configureAction
     * @covers ::retrieve
     * @covers ::configureURL
     */
    public function testRetrieve(){
        $Model = new ModelEndpoint();
        $Model->setBaseUrl('localhost/api/v1/');
        $Model->setProperty('url','model/$id');
        $Model->setRequest(new JSON());
        $this->assertEquals($Model,$Model->retrieve('1234'));
        $this->assertEquals('localhost/api/v1/model/1234',$Model->getRequest()->getURL());
        $this->assertEquals('1234',$Model['id']);

        $this->assertEquals(ModelEndpoint::MODEL_ACTION_RETRIEVE,$Model->getCurrentAction());

        $Model['id'] = '5678';
        $this->assertEquals($Model,$Model->retrieve());
        $this->assertEquals('localhost/api/v1/model/5678',$Model->getRequest()->getURL());
        $this->assertEquals(JSON::HTTP_GET,$Model->getRequest()->getMethod());
        $this->assertEquals('5678',$Model->get('id'));

        $this->assertEquals($Model,$Model->retrieve('0000'));
        $this->assertEquals('localhost/api/v1/model/0000',$Model->getRequest()->getURL());
        $this->assertEquals(JSON::HTTP_GET,$Model->getRequest()->getMethod());
        $this->assertEquals('0000',$Model->get('id'));
    }

    /**
     * @covers ::retrieve
     * @expectedException MRussell\REST\Exception\Endpoint\MissingModelId
     * @expectedExceptionMessageRegExp /Model ID missing for current action/
     */
    public function testMissingModelId(){
        $Model = new ModelEndpoint();
        $Model->setBaseUrl('localhost/api/v1/');
        $Model->setProperty('url','model/$id');
        $Model->setRequest(new JSON());
        $Model->retrieve();
    }

    /**
     * @covers ::save
     * @covers ::configureAction
     * @covers ::configureURL
     * @covers ::configureData
     */
    public function testSave(){
        $Model = new ModelEndpoint();
        $Model->setRequest(new JSON());
        $Model->setBaseUrl('localhost/api/v1/');
        $Model->setProperty('url','model/$id');
        $Model->set('foo','bar');
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS);

        $this->assertEquals($Model,$Model->save());
        $this->assertEquals('create',$Model->getCurrentAction());
        $this->assertEquals('localhost/api/v1/model',$Model->getRequest()->getURL());
        $this->assertEquals(JSON::HTTP_POST,$Model->getRequest()->getMethod());
        $this->assertEquals(array(
            'foo' => 'bar'
        ),$Model->getRequest()->getBody());

        $Model->set('id','1234');
        $this->assertEquals($Model,$Model->save());
        $this->assertEquals('update',$Model->getCurrentAction());
        $this->assertEquals('localhost/api/v1/model/1234',$Model->getRequest()->getURL());
        $this->assertEquals(JSON::HTTP_PUT,$Model->getRequest()->getMethod());
        $this->assertEquals(array(
            'id' => '1234',
            'foo' => 'bar'
        ),$Model->getRequest()->getBody());
    }

    /**
     * @covers ::delete
     * @covers ::configureAction
     */
    public function testDelete(){
        $Model = new ModelEndpoint();
        $Model->setRequest(new JSON());
        $Model->setBaseUrl('localhost/api/v1/');
        $Model->setProperty('url','model/$id');
        $Model->set('id','1234');

        $this->assertEquals($Model,$Model->delete());
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE,$Model->getCurrentAction());
        $this->assertEquals('localhost/api/v1/model/1234',$Model->getRequest()->getURL());
        $this->assertEquals(JSON::HTTP_DELETE,$Model->getRequest()->getMethod());
    }

    /**
     * @covers ::configureResponse
     * @covers ::updateModel
     */
    public function testConfigureResponse(){
        $Model = new \MRussell\REST\Endpoint\JSON\ModelEndpoint();
        $Model->setBaseUrl('localhost/api/v1/');
        $Model->setProperty('url','model/$id');
        $Response = $Model->getResponse();

        $ReflectedResponse = new \ReflectionClass('MRussell\Http\Response\JSON');
        $ReflectedModel = new \ReflectionClass('MRussell\REST\Endpoint\JSON\ModelEndpoint');
        $status = $ReflectedResponse->getProperty('status');
        $status->setAccessible(TRUE);
        $status->setValue($Response,'200');

        $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_CREATE);
        $method = $ReflectedModel->getMethod('configureResponse');
        $method->setAccessible(TRUE);
        $Model->setResponse($Response);
        $this->assertEquals($Response,$method->invoke($Model,$Response));
        $this->assertNotEmpty($Response->getRequest());

        $status->setValue($Response,'200');
        $body = $ReflectedResponse->getProperty('body');
        $body->setAccessible(TRUE);
        $body->setValue($Response,json_encode(array(
                'id' => '1234',
                'name' => 'foo',
                'foo' => 'bar'
            )
        ));
        $Model->setResponse($Response);
        $updateModel = $ReflectedModel->getMethod('updateModel');
        $updateModel->setAccessible(TRUE);
        $updateModel->invoke($Model);
        $this->assertEquals(array(
            'id' => '1234',
            'name' => 'foo',
            'foo' => 'bar'
        ),$Model->asArray());
        $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_DELETE);
        $updateModel->invoke($Model);
        $this->assertEquals(array(),$Model->asArray());
        $this->assertEmpty($Model->get('id'));

        $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_UPDATE);
        $updateModel->invoke($Model);
        $this->assertEquals(array(
            'id' => '1234',
            'name' => 'foo',
            'foo' => 'bar'
        ),$Model->asArray());

        $Model->clear();
        $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_RETRIEVE);
        $updateModel->invoke($Model);
        $this->assertEquals(array(
            'id' => '1234',
            'name' => 'foo',
            'foo' => 'bar'
        ),$Model->asArray());
    }
}
