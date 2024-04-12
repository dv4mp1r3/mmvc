<?php

declare(strict_types=1);

namespace tests\models\session;

use mmvc\models\data\KeyValueStorage;
use PHPUnit\Framework\TestCase;

class SessionStorageTest extends TestCase
{
    const KEY = 'key';

    private KeyValueStorage $storage;

    public function setUp(): void
    {
        $this->storage = new ArrayBasedStorage();
    }

    private function fillStorage(): KeyValueStorage
    {
        return $this->storage->set(self::KEY, 1);
    }

    /**
     * @depends testExists
     */
    public function testGet(): void
    {
        $this->fillStorage();
        $this->assertEquals(1, $this->storage->get(self::KEY));
    }

    public function testSet(): void
    {
        $this->assertInstanceOf(KeyValueStorage::class, $this->fillStorage());
    }

    /**
     * @depends testSet
     */
    public function testExists(): void
    {
        $this->fillStorage();
        $this->assertEquals(true, $this->storage->exists(self::KEY));
    }
}