<?php

namespace mmvc\models\data;


interface Transactional
{
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function inTransaction(): bool;
}