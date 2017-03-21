<?php

namespace MRussell\REST\Tests\Storage;

use MRussell\REST\Storage\StaticStorage;

/**
 * Class StaticStorageTest
 * @package MRussell\REST\Tests\Storage
 * @coversDefaultClass MRussell\REST\Storage\StaticStorage
 */
class StaticStorageTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
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
     * @covers ::store
     * @covers ::get
     */
    public function testStore(){
        $Storage = new StaticStorage();
        $this->assertEquals(TRUE,$Storage->store('foo','bar'));
        $this->assertEquals('bar',$Storage->get('foo'));
        $this->assertEquals(TRUE,$Storage->store('bar','foo'));
        $this->assertEquals('foo',$Storage->get('bar'));
        unset($Storage);
        $Storage = new StaticStorage();
        $this->assertEquals('bar',$Storage->get('foo'));
        $this->assertEquals('foo',$Storage->get('bar'));
    }

    /**
     * @covers ::getNamespace
     */
    public function testGetNamespace(){
        $Storage = new StaticStorage();
        $this->assertEquals('global',$Storage->getNamespace());
    }

    /**
     * @covers ::setItem
     * @covers ::getItem
     */
    public function testSetItem() {
        $Storage = new StaticStorage();
        $this->assertEquals('bar',$Storage->get('foo'));
        $this->assertEquals('foo',$Storage->get('bar'));
        $this->assertEquals('bar',StaticStorage::getItem('global', 'foo'));
        $this->assertEquals('foo',StaticStorage::getItem('global', 'bar'));
        $this->assertEquals(TRUE,StaticStorage::setItem('global', 'baz', 'foz'));
        $this->assertEquals(TRUE,StaticStorage::setItem('test', 'foz', 'baz'));
        $this->assertEquals('foz', $Storage->get('baz'));
        $this->assertEquals('foz', StaticStorage::getItem('global', 'baz'));
        $this->assertEmpty($Storage->get('foz'));
        $this->assertEquals('baz',StaticStorage::getItem('test', 'foz'));
    }

    /**
     * @covers ::remove
     * @covers ::removeItem
     * @covers ::clear
     */
    public function testRemove(){
        $Storage = new StaticStorage();
        $this->assertEquals(TRUE,$Storage->remove('foo'));
        $this->assertEmpty($Storage->get('foo'));
        $this->assertEquals(TRUE,StaticStorage::removeItem('test','foz'));
        $this->assertEmpty(StaticStorage::getItem('test','foz'));
        $this->assertEquals(TRUE,StaticStorage::clear('test'));
        $this->assertEquals(TRUE,StaticStorage::clear($Storage->getNamespace()));
        $this->assertEmpty($Storage->get('bar'));
        $this->assertEmpty(StaticStorage::getItem('global','baz'));
    }
}
