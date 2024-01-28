<?php

declare(strict_types=1);

namespace mmvc\models\data;

interface KeyValueStorage
{
    /**
     * @param string $key
     * @return mixed
     */
    function get(string $key);

    /**
     * @param string $key
     * @param $value
     * @return KeyValueStorage
     */
    function set(string $key, $value) : self;

    /**
     * @param string $key
     * @return bool
     */
    function exists(string $key): bool;
}