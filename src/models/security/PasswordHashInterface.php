<?php

declare(strict_types=1);

namespace mmvc\models\security;

interface PasswordHashInterface
{
    public function getPasswordHash(string $password) : string;
}
