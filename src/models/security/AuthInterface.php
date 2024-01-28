<?php

declare(strict_types=1);

namespace mmvc\models\security;

interface AuthInterface
{
    public function login() : void;

    public function logout() : void;
}
