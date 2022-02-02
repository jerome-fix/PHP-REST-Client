<?php

namespace MRussell\REST\Tests\Client;

use MRussell\REST\Tests\Stubs\Auth\AuthController;
use MRussell\REST\Tests\Stubs\Client\Client;
use MRussell\REST\Tests\Stubs\Endpoint\EndpointProviderWithDefaults;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractClientTest
 * @package MRussell\REST\Tests\Client
 * @coversDefaultClass MRussell\REST\Client\AbstractClient
 * @group AbstractClientTest
 */
class AbstractClientTest extends TestCase {

    public static function setUpBeforeClass(): void {
        //Add Setup for static properties here
    }

    public static function tearDownAfterClass(): void {
        //Add Tear Down for static properties here
    }

    /**
     * @var Client
     */
    protected $Client;

    protected $server = 'localhost';

    protected $version = '1.0';

    public function setUp(): void {
        $this->Client = new Client();
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::setAuth
     * @covers ::getAuth
     * @return Client
     */
    public function testSetAuth() {
        $Auth = new AuthController();
        $this->assertEquals($this->Client, $this->Client->setAuth($Auth));
        $this->assertEquals($Auth, $this->Client->getAuth());
        return $this->Client;
    }

    /**
     * @param Client $Client
     * @covers ::setEndpointProvider
     * @covers ::getEndpointProvider
     * @return Client
     */
    public function testSetEndpointProvider() {
        $EndpointProvider = new EndpointProviderWithDefaults();
        $this->assertEquals($this->Client, $this->Client->setEndpointProvider($EndpointProvider));
        $this->assertEquals($EndpointProvider, $this->Client->getEndpointProvider());
        return $this->Client;
    }

    /**
     * @covers ::setServer
     * @covers ::getServer
     * @covers ::setAPIUrl
     * @covers ::getAPIUrl
     */
    public function testSetServer() {
        $this->assertEquals($this->Client, $this->Client->setServer(null));
        $this->assertEquals(null, $this->Client->getServer());
        $this->assertEquals(null, $this->Client->getAPIUrl());
        $this->assertEquals($this->Client, $this->Client->setServer($this->server));
        $this->assertEquals($this->server, $this->Client->getServer());
        $this->assertEquals($this->server, $this->Client->getAPIUrl());
    }

    /**
     * @covers ::setVersion
     * @covers ::getVersion
     */
    public function testSetVersion() {
        $this->assertEquals($this->Client, $this->Client->setVersion(1));
        $this->assertEquals(1, $this->Client->getVersion());
        $this->assertEquals($this->Client, $this->Client->setVersion(null));
        $this->assertEquals(null, $this->Client->getVersion());
        $this->assertEquals($this->Client, $this->Client->setVersion(array()));
        $this->assertEquals(array(), $this->Client->getVersion());
        $this->assertEquals($this->Client, $this->Client->setVersion($this->version));
        $this->assertEquals($this->version, $this->Client->getVersion());
    }

    /**
     * @param Client $Client
     * @depends testSetEndpointProvider
     * @covers ::__call
     * @covers ::last
     * @covers ::current
     * @covers ::setCurrentEndpoint
     */
    public function testCall(Client $Client) {
        $this->Client = $Client;
        $AuthEP = $this->Client->auth();
        $this->assertNotEmpty($AuthEP);
        $this->assertEquals($AuthEP, $this->Client->current());
        $AuthEP2 = $this->Client->auth();
        $this->assertNotEmpty($AuthEP2);
        $this->assertEquals($AuthEP2, $this->Client->current());
        $this->assertEquals($AuthEP, $this->Client->last());
    }

    /**
     * @throws MRussell\REST\Exception\Client\EndpointProviderMissing
     */
    public function testProviderMissingException() {
        $this->Client = new Client();
        $this->expectException(\MRussell\REST\Exception\Client\EndpointProviderMissing::class);
        $this->expectExceptionMessage("Endpoint Provider not configured on Client object.");
        $this->Client->auth();
    }
}
