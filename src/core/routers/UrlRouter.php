<?php

declare(strict_types=1);

namespace mmvc\core\routers;

use mmvc\core\Config;

/**
 * Обработка урл вида index.php?u=ctrlName-view
 */
class UrlRouter extends AbstractFromValueRouter
{
    private string $valueName;
    public function __construct(Config $config, string $delimiter = '-', string $valueName = 'u')
    {
        parent::__construct($config, $delimiter);
        $this->valueName = $valueName;
    }

    function getValue(): string
    {
        return $_GET[$this->valueName] ?? '';
    }
}
