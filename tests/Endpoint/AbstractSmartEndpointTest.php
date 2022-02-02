<?php

namespace MRussell\REST\Tests\Endpoint;

use MRussell\REST\Endpoint\Data\EndpointData;
use MRussell\REST\Tests\Stubs\Endpoint\SmartEndpoint;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class AbstractSmartEndpointTest
 * @package MRussell\REST\Tests\Endpoint
 * @coversDefaultClass MRussell\REST\Endpoint\Abstracts\AbstractSmartEndpoint
 * @group AbstractSmartEndpointTest
 */
class AbstractSmartEndpointTest extends TestCase {

    public static function setUpBeforeClass(): void {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass(): void {
        //Add Tear Down for static properties here
    }

    protected $properties = [
        'data' => [
            'required' => [
                'foo' => 'string'
            ],
            'defaults' => [
                'bar' => 'foo'
            ]
        ]
    ];

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::configureDataProperties
     */
    public function testConstructor() {
        $Endpoint = new SmartEndpoint();
        $this->assertNotEmpty($Endpoint->getData());
        $Endpoint = new SmartEndpoint(['foo'], ['url' => 'bar']);
        $this->assertNotEmpty($Endpoint->getData());
        $this->assertEquals($Endpoint->getEndPointUrl(), 'bar');
        $this->assertEquals($Endpoint->getUrlArgs(), ['foo']);
        $Endpoint = new SmartEndpoint(
            ['foo'],
            $this->properties
        );
        $this->assertNotEmpty($Endpoint->getData());
        $this->assertEquals($Endpoint->getUrlArgs(), ['foo']);
        $this->assertEquals($Endpoint->getUrlArgs(), ['foo']);
        $this->assertEquals($Endpoint->getData()->toArray(), ['bar' => 'foo']);
        $this->assertEquals($Endpoint->getProperties()['data'], $this->properties['data']);
    }

    /**
     * @covers ::setProperties
     * @covers ::setProperty
     * @covers ::configureDataProperties
     */
    public function testSetProperties() {
        $Endpoint = new SmartEndpoint();
        $Endpoint->setProperties([]);
        $this->assertEquals(array(
            'url' => '',
            'httpMethod' => '',
            'auth' => false,
            'data' => [
                'required' => [],
                'defaults' => []
            ]
        ), $Endpoint->getProperties());

        $this->assertEquals([
            'required' => [],
            'defaults' => []
        ], $Endpoint->getData()->getProperties());

        $Endpoint->setProperties($this->properties);
        $this->assertEquals([
            'url' => '',
            'httpMethod' => '',
            'auth' => false,
            'data' => [
                'required' => [
                    'foo' => 'string'
                ],
                'defaults' => [
                    'bar' => 'foo'
                ]
            ]
        ], $Endpoint->getProperties());
        
        $this->assertEquals([
            'required' => [
                'foo' => 'string'
            ],
            'defaults' => [
                'bar' => 'foo'
            ]
        ], $Endpoint->getData()->getProperties());
    }

    /**
     * @covers ::setData
     * @covers ::getData
     * @covers ::configureData
     */
    public function testSetData() {
        $Endpoint = new SmartEndpoint();
        $Data = new EndpointData();
        $this->assertEquals($Endpoint, $Endpoint->setData($Data));
        $this->assertEquals([
            'required' => [],
            'defaults' => []
        ], $Endpoint->getData()->getProperties());

        $this->assertEquals($Endpoint, $Endpoint->setData(['foo' => 'bar']));
        $this->assertEquals([
            'foo' => 'bar'
        ], $Endpoint->getData()->toArray());
    }

    /**
     * @throws MRussell\REST\Exception\Endpoint\InvalidDataType
     */
    public function testInvalidDataType() {
        $Endpoint = new SmartEndpoint();
        $this->expectException(\MRussell\REST\Exception\Endpoint\InvalidDataType::class);
        $this->expectExceptionMessage("Invalid data type passed to Endpoint [MRussell\REST\Tests\Stubs\Endpoint\SmartEndpoint]");
        $Endpoint->setData('test');
    }
}
