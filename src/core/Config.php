<?php

declare(strict_types=1);

namespace mmvc\core;

/**
 * Враппер для массива, использующегося в качестве конфига
 * @package mmvc\core
 */
class Config
{
    /**
     * @var array
     */
    private array $raw;

    public function __construct(array $raw) {
        $this->raw = $raw;
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
    public function getValueByKey($key) {
        if (array_key_exists($key, $this->raw)) {
            return $this->raw[$key];
        }
        return null;
    }

}