<?php

declare(strict_types=1);

namespace mmvc\models\security;

use mmvc\models\BaseModel;
use mmvc\models\data\KeyValueStorage;

class CsrfToken extends BaseModel
{
    const SESSION_KEY = 'csrfToken';

    private KeyValueStorage $storage;

    public function __construct(KeyValueStorage $storage)
    {
        parent::__construct();
        $this->storage = $storage;
    }

    public function initToken() : string {
        if (function_exists('mcrypt_create_iv')) {
            $this->storage->set(self::SESSION_KEY, bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)));
        } else {
            $this->storage->set(self::SESSION_KEY, bin2hex(openssl_random_pseudo_bytes(32)));
        }
        return $this->storage->get(self::SESSION_KEY);
    }

    public function isCorrectToken(string $token) : bool {
        return !empty($token)
            && $this->storage->exists(self::SESSION_KEY)
            && hash_equals($this->storage->get(self::SESSION_KEY), $token);
    }
}
