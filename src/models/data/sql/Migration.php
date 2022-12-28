<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

/**
 * Интерфейс миграции без сохранения статуса об исполнении
 * @package mmvc\models\data\sql
 */
interface Migration
{
    public function up() : bool;

    public function down() : bool;
}