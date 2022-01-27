<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\REST\Endpoint\Data\EndpointData as StockData;
use MRussell\REST\Tests\Stubs\Endpoint\EndpointData as StubData;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEndpointDataTest
 * @package MRussell\REST\Tests\Endpoint\Data
 * @coversDefaultClass MRussell\REST\Endpoint\Data\AbstractEndpointData
 * @group AbstractEndpointDataTest
 */
class AbstractEndpointDataTest extends TestCase
{

    public static function setUpBeforeClass():void
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass():void
    {
        //Add Tear Down for static properties here
    }

    protected $data = array(
        'foo' => 'bar',
        'baz' => 'foz'
    );

    protected $properties = array(
        'required' => array(
            'foo' => 'string'
        ),
        'defaults' => array(
            'bar' => 'foo'
        )
    );

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
     * @covers ::configureDefaultData
     * @covers ::update
     * @covers ::asArray
     * @covers ::getProperties
     */
    public function testConstructor(){
        $Data = new StockData();
        $this->assertEquals(array(
            StockData::DATA_PROPERTY_REQUIRED => array(),
            StockData::DATA_PROPERTY_DEFAULTS => array()
        ), $Data->getProperties());
        $this->assertEquals(array(),$Data->toArray());
        $Data = new StockData($this->properties);
        $this->assertEquals($this->properties, $Data->getProperties());
        $this->assertEquals(array(
            'bar' => 'foo'
        ),$Data->toArray());
        $Data = new StockData($this->properties,$this->data);
        $this->assertEquals($this->properties, $Data->getProperties());
        $data = $this->data;
        $data['bar'] = 'foo';
        $this->assertEquals($data,$Data->toArray());
        $Data = new StockData(array(),$this->data);
        $this->assertEquals(array(
            StockData::DATA_PROPERTY_REQUIRED => array(),
            StockData::DATA_PROPERTY_DEFAULTS => array()
        ), $Data->getProperties());
        $this->assertEquals($this->data,$Data->toArray());

        $Data = new StubData(array());
        $this->assertEquals($this->properties, $Data->getProperties());
        $this->assertEquals(array(
            'bar' => 'foo'
        ),$Data->toArray());
    }

    /**
     * @covers ::__get
     * @covers ::__set
     * @covers ::__isset
     * @covers ::__unset
     * @covers ::offsetSet
     * @covers ::offsetExists
     * @covers ::offsetUnset
     * @covers ::offsetGet
     * @covers ::asArray
     */
    public function testDataAccess(){
        $this->data = array_replace($this->data,array(
            'test' => 'tester',
            'abcd' => 'efg',
            'pew' => 'die',
            'arr' => array(),
            'iint' => 1234
        ));
        $Data = new StockData(array(),$this->data);
        $Data['bar'] = 'foo';
        $this->assertEquals('foo',$Data['bar']);
        $this->assertEquals('foo',$Data->bar);
        $Data->foz = 'baz';
        $this->assertEquals('baz',$Data['foz']);
        $this->assertEquals('baz',$Data->foz);
        $Data[] = 'number1';
        $this->assertEquals('number1',$Data[0]);
        $this->assertEquals('tester',$Data->test);
        $this->assertEquals(TRUE,isset($Data->abcd));
        $this->assertEquals('die',$Data['pew']);
        $this->assertEquals(array(),$Data['arr']);
        $this->assertEquals(array(),$Data->arr);
        $this->assertEquals(TRUE,isset($Data['iint']));
        unset($Data->arr);
        $this->assertEquals(FALSE,isset($Data->arr));
        unset($Data['abcd']);
        $this->assertEquals(FALSE,isset($Data['abcd']));
        $this->assertEquals(array(
            0 => 'number1',
            'foo' => 'bar',
            'bar' => 'foo',
            'baz' => 'foz',
            'foz' => 'baz',
            'test' => 'tester',
            'pew' => 'die',
            'iint' => 1234
        ),$Data->toArray());
    }

    /**
     * @covers ::setProperties
     * @covers ::getProperties
     */
    public function testSetProperties(){
        $Data = new StockData();
        $this->assertEquals(array(
            StockData::DATA_PROPERTY_REQUIRED => array(),
            StockData::DATA_PROPERTY_DEFAULTS => array()
        ), $Data->getProperties());
        $this->assertEquals($Data,$Data->setProperties(array()));
        $this->assertEquals(array(
            StockData::DATA_PROPERTY_REQUIRED => array(),
            StockData::DATA_PROPERTY_DEFAULTS => array()
        ), $Data->getProperties());
        $this->assertEquals($Data,$Data->setProperties($this->properties));
        $this->assertEquals($this->properties,$Data->getProperties());
    }

    /**
     * @depends testDataAccess
     * @covers ::reset
     * @covers ::clear
     */
    public function testReset(){
        $Data = new StockData();
        $Data['foo'] = 'bar';
        $Data->setProperties($this->properties);
        $this->assertEquals($Data,$Data->reset());
        $this->assertEquals(array(
            StockData::DATA_PROPERTY_REQUIRED => array(),
            StockData::DATA_PROPERTY_DEFAULTS => array()
        ), $Data->getProperties());
        $this->assertEquals(array(),$Data->toArray());

        $Data = new StubData(array(),$this->data);
        $Data->setProperties(array());
        $this->assertEquals($Data,$Data->reset());
        $this->assertEquals($this->properties, $Data->getProperties());
        $this->assertEquals(array('bar' => 'foo'),$Data->toArray());
        $this->assertEquals($Data,$Data->clear());
        $this->assertEquals(array(),$Data->toArray());
    }

    /**
     * @covers ::verifyRequiredData
     * @covers ::asArray
     */
    public function testVerifyRequiredData(){
        $Data = new StubData();
        $Data['foo'] = 'bar';
        $this->assertEquals(array(
            'foo' => 'bar',
            'bar' => 'foo'
        ),$Data->toArray(TRUE));
    }

    /**
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidData
     * @expectedExceptionMessageRegExp /Missing or Invalid data on Endpoint Data\. Errors: (Missing \[[A-z0-9,].*\]|Invalid \[[A-z0-9,].*\])/
     */
    public function testMissingData(){
        $Data = new StubData();
        $Data->toArray(TRUE);
    }

    /**
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidData
     * @expectedExceptionMessageRegExp /Missing or Invalid data on Endpoint Data\. Errors: (Missing \[[A-z0-9,].*\]|Invalid \[[A-z0-9,].*\])/
     */
    public function testInvalidData(){
        $Data = new StubData();
        $Data['foo'] = 1234;
        $Data->toArray(TRUE);
    }

    /**
     * @expectedException MRussell\REST\Exception\Endpoint\InvalidData
     * @expectedExceptionMessageRegExp /Missing or Invalid data on Endpoint Data\. Errors: (Missing \[[A-z0-9,].*\]|Invalid \[[A-z0-9,].*\])/
     */
    public function testInvalidAndMissingData(){
        $Data = new StubData();
        $properties = $Data->getProperties();
        $properties['required']['bar'] = NULL;
        $Data->setProperties($properties);
        $Data['foo'] = 1234;
        $Data->toArray(TRUE);
    }
}
