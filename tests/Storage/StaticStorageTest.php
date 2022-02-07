<?php

namespace MRussell\REST\Tests\Storage;

use MRussell\REST\Storage\StaticStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class StaticStorageTest
 * @package MRussell\REST\Tests\Storage
 * @coversDefaultClass \MRussell\REST\Storage\StaticStorage
 */
class StaticStorageTest extends TestCase {

    public static function setUpBeforeClass(): void {
    }

    public static function tearDownAfterClass(): void {
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::store
     * @covers ::get
     */
    public function testStore() {
        $Storage = new StaticStorage();
        $this->assertEquals(true, $Storage->store('foo', 'bar'));
        $this->assertEquals('bar', $Storage->get('foo'));
        $this->assertEquals(true, $Storage->store('bar', 'foo'));
        $this->assertEquals('foo', $Storage->get('bar'));
        unset($Storage);
        $Storage = new StaticStorage();
        $this->assertEquals('bar', $Storage->get('foo'));
        $this->assertEquals('foo', $Storage->get('bar'));
    }

    /**
     * @covers ::getNamespace
     */
    public function testGetNamespace() {
        $Storage = new StaticStorage();
        $this->assertEquals('global', $Storage->getNamespace());
    }

    /**
     * @covers ::setItem
     * @covers ::getItem
     */
    public function testSetItem() {
        $Storage = new StaticStorage();
//        $this->assertEquals('bar', $Storage->get('foo'));
//        $this->assertEquals('foo', $Storage->get('bar'));
//        $this->assertEquals('bar', StaticStorage::getItem('global', 'foo'));
//        $this->assertEquals('foo', StaticStorage::getItem('global', 'bar'));
        $this->assertEquals(true, StaticStorage::setItem('global', 'baz', 'foz'));
        $this->assertEquals(true, StaticStorage::setItem('test', 'foz', 'baz'));
        $this->assertEquals('foz', $Storage->get('baz'));
        $this->assertEquals('foz', StaticStorage::getItem('global', 'baz'));
        $this->assertEmpty($Storage->get('foz'));
        unset($Storage);
        $Storage = new StaticStorage();
        $this->assertEquals('baz', StaticStorage::getItem('test', 'foz'));
    }

    /**
     * @covers ::remove
     * @covers ::removeItem
     * @covers ::clear
     */
    public function testRemove() {
        $Storage = new StaticStorage();
        $this->assertEquals(true, $Storage->remove('foo'));
        $this->assertEmpty($Storage->get('foo'));
        $this->assertEquals(true, StaticStorage::removeItem('test', 'foz'));
        $this->assertEmpty(StaticStorage::getItem('test', 'foz'));
        $this->assertEquals(true, StaticStorage::clear('test'));
        $this->assertEquals(true, StaticStorage::clear($Storage->getNamespace()));
        $this->assertEmpty($Storage->get('bar'));
        $this->assertEmpty(StaticStorage::getItem('global', 'baz'));
    }
}
