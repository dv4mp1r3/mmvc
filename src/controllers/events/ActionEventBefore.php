<?php

declare(strict_types=1);

namespace mmvc\controllers\events;

interface ActionEventBefore
{
    public function beforeAction(string $action);

}