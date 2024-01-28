<?php

declare(strict_types=1);

namespace mmvc\core\routers;

class CliRouter extends AbstractFromValueRouter
{

    function getValue(): string
    {
        return isset($_SERVER['argv']) && count($_SERVER['argv']) >= 2
            ? $_SERVER['argv'][1]
            : '';
    }
}
