<?php

declare(strict_types=1);

namespace tests\models\data;

use mmvc\models\data\RDBSchemaRecord;

class RDBSchemaMock extends RDBSchemaRecord
{
    public function __construct($id = null, $table = null, $dbConfig = null)
    {
        //parent::__construct($id, $table, $dbConfig);
    }

    public static function addTableSchema(string $table, array $schema) : void {
        self::$schema[$table] = $schema;
    }
}