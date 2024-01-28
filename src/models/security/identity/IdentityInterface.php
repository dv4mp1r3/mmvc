<?php


namespace mmvc\models\security\identity;


interface IdentityInterface
{
    public function isAuthorized(): bool;
}
