<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\REST\Tests\Stubs\Endpoint\SmartEndpoint;


/**
 * Class AbstractSmartEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractSmartEndpoint
 * @group AbstractSmartEndpointTest
 */
class AbstractSmartEndpointTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass()
    {
        //Add Tear Down for static properties here
    }

    protected $properties = array(
        'data' => array(
            'required' => array(
                'foo' => 'string'
            ),
            'defaults' => array(
                'bar' => 'foo'
            )
        )
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
     * @covers ::configureDataProperties
     */
    public function testConstructor(){
        $Endpoint = new SmartEndpoint();
        $this->assertNotEmpty($Endpoint->getData());
        $Endpoint = new SmartEndpoint(array('foo'),array('url' => 'bar'));
        $this->assertNotEmpty($Endpoint->getData());
        $Endpoint = new SmartEndpoint(
            array('foo'),
            $this->properties
        );
        $this->assertNotEmpty($Endpoint->getData());
        $this->assertEquals($this->properties['data'],$Endpoint->getData()->getProperties());
    }

    /**
     * @covers ::setProperties
     * @covers ::setProperty
     * @covers ::configureDataProperties
     */
    public function testSetProperties(){
        $Endpoint = new SmartEndpoint();
        $this->assertEquals($Endpoint,$Endpoint->setProperties(array()));
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => FALSE,
            'data' => array(
                'required' => array(),
                'defaults' => array()
            )
        ),$Endpoint->getProperties());
        $this->assertEquals(array(
            'required' => array(),
            'defaults' => array()
        ),$Endpoint->getData()->getProperties());
        $this->assertEquals($Endpoint,$Endpoint->setProperties($this->properties));
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => FALSE,
            'data' => array(
                'required' => array(
                    'foo' => 'string'
                ),
                'defaults' => array(
                    'bar' => 'foo'
                )
            )
        ),$Endpoint->getProperties());
        $this->assertEquals(array(
            'required' => array(
                'foo' => 'string'
            ),
            'defaults' => array(
                'bar' => 'foo'
            )
        ),$Endpoint->getData()->getProperties());

        $this->assertEquals($Endpoint,$Endpoint->setProperty('data',array(
            'required' => array()
        )));
        $this->assertEquals(array(
            'required' => array(),
            'defaults' => array()
        ),$Endpoint->getData()->getProperties());
    }

    /**
     * @covers ::setData
     * @covers ::configureData
     */
    public function testSetData(){
        $Endpoint = new SmartEndpoint();

    }

}
