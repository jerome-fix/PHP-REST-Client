<?php

namespace MRussell\REST\Tests\Endpoint;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MRussell\REST\Tests\Stubs\Client\Client;
use MRussell\REST\Tests\Stubs\Endpoint\ModelEndpoint;
use MRussell\REST\Tests\Stubs\Endpoint\ModelEndpointWithActions;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractModelEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractModelEndpoint
 * @group AbstractModelEndpointTest
 */
class AbstractModelEndpointTest extends TestCase {
    protected static $_REFLECTED_CLASS = 'MRussell\REST\Tests\Stubs\Endpoint\ModelEndpoint';
    /**
     * @var Client
     */
    protected static $client;


    public static function setUpBeforeClass(): void {
        static::$client = new Client();
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass(): void {
        static::$client = null;
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        ModelEndpoint::modelIdKey('id');
        parent::tearDown();
    }

    /**
     * @covers ::modelIdKey
     */
    public function testModelIdKey() {
        $this->assertEquals('id', ModelEndpoint::modelIdKey());
        $this->assertEquals('key', ModelEndpoint::modelIdKey('key'));
        $this->assertEquals('key', ModelEndpoint::modelIdKey());
        $Model = new ModelEndpoint();
        $this->assertEquals('key', $Model->modelIdKey());
        $this->assertEquals('id', ModelEndpoint::modelIdKey('id'));
        $this->assertEquals('id', $Model->modelIdKey());
        $this->assertEquals('key', $Model->modelIdKey('key'));
        $this->assertEquals('key', ModelEndpoint::modelIdKey());
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor() {
        $Model = new ModelEndpoint();
        $Class = new \ReflectionClass(static::$_REFLECTED_CLASS);
        $actions = $Class->getProperty('actions');
        $actions->setAccessible(true);
        $this->assertEquals(array(
            'create' => "POST",
            'retrieve' => "GET",
            'update' => "PUT",
            'delete' => "DELETE"
        ), $actions->getValue($Model));
    }

    /**
     * @covers ::__call
     * @covers ::configureAction
     */
    public function testCall() {
        $Model = new ModelEndpointWithActions();
        $Class = new \ReflectionClass($Model);
        $actions = $Class->getProperty('actions');
        $actions->setAccessible(true);
        $this->assertEquals(array(
            'foo' => "GET",
            'create' => "POST",
            'retrieve' => "GET",
            'update' => "PUT",
            'delete' => "DELETE"
        ), $actions->getValue($Model));

        static::$client->mockResponses->append(new Response(200));
        $Model->setClient(static::$client);

        $this->assertEquals($Model, $Model->foo());
        $props = $Model->getProperties();
        $this->assertEquals("GET", $props['httpMethod']);
    }

    /**
     * @covers ::__call
     * @expectedException MRussell\REST\Exception\Endpoint\UnknownModelAction
     */
    public function testCallException() {
        $Model = new ModelEndpointWithActions();
        $this->expectException(\MRussell\REST\Exception\Endpoint\UnknownModelAction::class);
        $this->expectExceptionMessage("Unregistered Action called on Model Endpoint [MRussell\REST\Tests\Stubs\Endpoint\ModelEndpointWithActions]: bar");
        $Model->bar();
    }

    /**
     * @covers ::offsetSet
     * @covers ::offsetGet
     * @covers ::offsetUnset
     * @covers ::offsetExists
     * @covers ::set
     * @covers ::get
     * @covers ::toArray
     * @covers ::reset
     * @covers ::clear
     * @covers ::set
     */
    public function testDataAccess() {
        $Model = new ModelEndpoint();
        $this->assertEquals($Model, $Model->set('foo', 'bar'));
        $this->assertEquals(true, isset($Model['foo']));
        $this->assertEquals('bar', $Model['foo']);
        $this->assertEquals(array(
            'foo' => 'bar'
        ), $Model->toArray());
        $this->assertEquals($Model, $Model->clear());
        $this->assertEquals(array(), $Model->toArray());
        $Model['foo'] = 'bar';
        $this->assertEquals('bar', $Model->get('foo'));
        unset($Model['foo']);
        $this->assertEquals(false, isset($Model['foo']));
        $this->assertEquals(array(), $Model->toArray());

        $Model[] = array(
            'foo' => 'bar'
        );
        $this->assertEquals(array(array(
            'foo' => 'bar'
        )), $Model->toArray());
        $this->assertEquals($Model, $Model->set(array(
            'foo' => 'bar'
        )));
        $this->assertEquals('bar', $Model->get('foo'));
        $this->assertEquals(array(
            'foo' => 'bar'
        ), $Model[0]);
        $this->assertEquals($Model, $Model->reset());
        $this->assertEquals(array(), $Model->toArray());
    }

    /**
     * @covers ::setCurrentAction
     * @covers ::getCurrentAction
     */
    public function testCurrentAction() {
        $Model = new ModelEndpoint();
        $this->assertEquals($Model, $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_CREATE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_CREATE, $Model->getCurrentAction());
        $this->assertEquals($Model, $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_UPDATE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_UPDATE, $Model->getCurrentAction());
        $this->assertEquals($Model, $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_DELETE));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE, $Model->getCurrentAction());
        $this->assertEquals($Model, $Model->setCurrentAction('foo'));
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE, $Model->getCurrentAction());
    }

    /**
     * @covers ::configureAction
     * @covers ::retrieve
     * @covers ::configureURL
     * @group model
     */
    public function testRetrieve() {
        $Model = new ModelEndpoint();
        $Model->setClient(static::$client);
        
        static::$client->mockResponses->append(new Response(200, [], json_encode([['id' => 1234]])));
        $this->assertEquals($Model, $Model->retrieve('1234'));
        $request = current(static::$client->container)['request'];
        $this->assertEquals('http://phpunit.tests/account/1234', $request->getUri()->__toString());
        $this->assertEquals('1234', $Model['id']);
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_RETRIEVE, $Model->getCurrentAction());

        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([['id' => 5678]])));
        $Model['id'] = '5678';
        $this->assertEquals($Model, $Model->retrieve());
        $this->assertEquals('http://phpunit.tests/account/5678', current(static::$client->container)['request']->getUri()->__toString());
        $this->assertEquals("GET", current(static::$client->container)['request']->getMethod());
        $this->assertEquals('5678', $Model->get('id'));

        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([['id' => 0000]])));
        $this->assertEquals($Model, $Model->retrieve('0000'));
        $this->assertEquals('http://phpunit.tests/account/0000', current(static::$client->container)['request']->getUri()->__toString());
        $this->assertEquals("GET", current(static::$client->container)['request']->getMethod());
        $this->assertEquals('0000', $Model->get('id'));
    }

    /**
     * @covers ::retrieve
     * @expectedException MRussell\REST\Exception\Endpoint\MissingModelId
     * @expectedExceptionMessageRegExp /Model ID missing for current action/
     */
    public function testMissingModelId() {
        $Model = new ModelEndpoint();
        $this->expectException(\MRussell\REST\Exception\Endpoint\MissingModelId::class);
        $this->expectExceptionMessage("Model ID missing for current action [retrieve] on Endpoint: MRussell\REST\Tests\Stubs\Endpoint\ModelEndpoint");
        $Model->retrieve();
    }

    /**
     * @covers ::save
     * @covers ::configureAction
     * @covers ::configureURL
     * @covers ::configurePayload
     */
    public function testSave() {
        $Model = new ModelEndpoint();

        $Model->setClient(static::$client);
        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode(['id' => 1234])));
        $Model->set('foo', 'bar');

        $this->assertEquals($Model, $Model->save());
        $this->assertEquals('create', $Model->getCurrentAction());
        $this->assertEquals('http://phpunit.tests/account', current(static::$client->container)['request']->getUri()->__toString());
        $this->assertEquals("POST", current(static::$client->container)['request']->getMethod());
        $this->assertEquals('{"foo":"bar"}', current(static::$client->container)['request']->getBody()->getContents());

        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode(['id' => '1234'])));
        $Model->set('id', '1234');
        $this->assertEquals($Model, $Model->save());
        $this->assertEquals('update', $Model->getCurrentAction());
        $this->assertEquals('http://phpunit.tests/account/1234', current(static::$client->container)['request']->getUri()->__toString());
        $this->assertEquals("PUT", current(static::$client->container)['request']->getMethod());
        $this->assertEquals('{"foo":"bar","id":"1234"}', current(static::$client->container)['request']->getBody()->getContents());
    }

    /**
     * @covers ::delete
     * @covers ::configureAction
     */
    public function testDelete() {
        $Model = new ModelEndpoint();
        $Model->setClient(static::$client);
        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([['id' => 1234]])));
        $Model->set('id', '1234');

        $this->assertEquals($Model, $Model->delete());
        $this->assertEquals(ModelEndpoint::MODEL_ACTION_DELETE, $Model->getCurrentAction());
        $this->assertEquals('http://phpunit.tests/account/1234', current(static::$client->container)['request']->getUri()->__toString());
        $this->assertEquals("DELETE", current(static::$client->container)['request']->getMethod());
    }

    /**
     * @covers ::setResponse
     * @covers ::syncFromApi
     */
    public function testGetResponse() {
        $Model = new ModelEndpoint();
        $Model->setClient(static::$client);
        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([
            'id' => '1234',
            'name' => 'foo'
        ])));
        $Model->setData(['name' => 'foo']);
        $Model->save();
        $this->assertEquals( "POST",current(static::$client->container)['request']->getMethod());
        $this->assertEquals($Model->getResponse()->getStatusCode(), 200);
        $this->assertEquals($Model->get('id'), "1234");
        $this->assertEquals($Model->get('name'), "foo");
        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([
            'id' => '1234',
            'name' => 'foo',
            'foo' => 'bar'
        ])));
        $Model->set([
            'foo' => 'bar'
        ]);
        $Model->save();
        $this->assertEquals($Model->getResponse()->getStatusCode(), 200);
        $this->assertEquals(current(static::$client->container)['request']->getMethod(), "PUT");

        static::$client->container = [];
        static::$client->mockResponses->append(new Response(200, [], json_encode([])));
        $Model->delete();
        $this->assertEquals($Model->getResponse()->getStatusCode(), 200);
        $this->assertEquals(current(static::$client->container)['request']->getMethod(), "DELETE");
        $this->assertEquals([], $Model->toArray());
        $this->assertEmpty($Model->get('id'));
        
        // $status->setValue($Response, '200');
        // $body = $ReflectedResponse->getProperty('body');
        // $body->setAccessible(true);
        // $body->setValue($Response, json_encode(
        //     array(
                
        //     )
        // ));
        // $Model->setResponse($Response);
        // $updateModel = $ReflectedModel->getMethod('updateModel');
        // $updateModel->setAccessible(true);
        // $updateModel->invoke($Model);
        // $this->assertEquals(array(
        //     'id' => '1234',
        //     'name' => 'foo',
        //     'foo' => 'bar'
        // ), $Model->toArray());
        // $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_DELETE);
        // $updateModel->invoke($Model);
        // $this->assertEquals(array(), $Model->toArray());
        // $this->assertEmpty($Model->get('id'));

        // $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_UPDATE);
        // $updateModel->invoke($Model);
        // $this->assertEquals(array(
        //     'id' => '1234',
        //     'name' => 'foo',
        //     'foo' => 'bar'
        // ), $Model->toArray());

        // $Model->clear();
        // $Model->setCurrentAction(ModelEndpoint::MODEL_ACTION_RETRIEVE);
        // $updateModel->invoke($Model);
        // $this->assertEquals(array(
        //     'id' => '1234',
        //     'name' => 'foo',
        //     'foo' => 'bar'
        // ), $Model->toArray());
    }
}
