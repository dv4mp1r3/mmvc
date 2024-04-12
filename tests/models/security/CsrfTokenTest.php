<?php

declare(strict_types=1);

namespace tests\models\security;

use mmvc\models\security\CsrfToken;
use PHPUnit\Framework\TestCase;
use tests\models\session\ArrayBasedStorage;

class CsrfTokenTest extends TestCase
{
    public function testInitToken(): void
    {
        $this->assertTrue(is_string((new CsrfToken(new ArrayBasedStorage()))->initToken()));
    }

    public function testIsCorrectToken(): void
    {
        $inst = new CsrfToken(new ArrayBasedStorage());
        $token = $inst->initToken();
        $this->assertTrue($inst->isCorrectToken($token));
    }
}