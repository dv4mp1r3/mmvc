<?php

declare(strict_types=1);

namespace mmvc\core;

/**
 * Враппер для массива, использующегося в качестве конфига
 * @package mmvc\core
 */
final class Config
{
    /**
     * @var array
     */
    private array $raw;

    private static ?Config $instance = null;

    public static function getInstance(array $raw = []) : Config
    {
        if (self::$instance === null) {
            self::$instance = new self($raw);
        }
        return self::$instance;
    }

    private function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->raw;
    }

    /**
     * @param string $key
     * @return null|mixed
     */
    public function getValueByKey(string $key) {
        if (array_key_exists($key, $this->raw)) {
            return $this->raw[$key];
        }
        return null;
    }

}