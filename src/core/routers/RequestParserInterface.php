<?php

declare(strict_types=1);

namespace mmvc\core\routers;

interface RequestParserInterface
{
    public function getActionName(): ?string;

    public function getControllerName(): ?string;

    public function getArgs(): array;
}
