<?php

declare(strict_types=1);

namespace mmvc\models\session;

use mmvc\models\data\KeyValueStorage;

class DefaultSessionStorage implements KeyValueStorage
{

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    function get(string $key)
    {
        return $_SESSION[$key];
    }

    function set(string $key, $value): KeyValueStorage
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    function exists(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }
}