<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

interface QueryValuesStore
{
    public function getQueryValues(): array;

    public function clearQueryValues(): void;

    public function addQueryValue(string $name, $value): void;
}