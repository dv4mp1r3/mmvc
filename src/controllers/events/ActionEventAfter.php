<?php

declare(strict_types=1);

namespace mmvc\controllers\events;

interface ActionEventAfter
{
    public function afterAction(string $action, $actionResult);

}