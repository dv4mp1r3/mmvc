<?php

declare(strict_types=1);

namespace tests\models\session;

use mmvc\models\data\KeyValueStorage;

class ArrayBasedStorage implements KeyValueStorage
{
    private array $data = [];

    function get(string $key)
    {
        return $this->data[$key];
    }

    function set(string $key, $value): KeyValueStorage
    {
        $this->data[$key] = $value;
        return $this;
    }

    function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}