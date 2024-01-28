<?php

declare(strict_types=1);

namespace mmvc\models\security;

use mmvc\models\BaseModel;

class CsrfToken extends BaseModel
{
    const SESSION_KEY = 'csrfToken';

    public function __construct()
    {
        parent::__construct();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function initToken() : string {
        if (function_exists('mcrypt_create_iv')) {
            $_SESSION[self::SESSION_KEY] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        } else {
            $_SESSION[self::SESSION_KEY] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public function isCorrectToken(string $token) : bool {
        return !empty($token)
            && array_key_exists(self::SESSION_KEY, $_SESSION)
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
}
