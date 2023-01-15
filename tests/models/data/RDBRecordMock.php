<?php

declare(strict_types=1);

namespace tests\models\data;

use mmvc\models\data\RDBRecord;

class RDBRecordMock extends RDBRecord
{
    public function __construct($id = null, $table = null, $dbConfig = null)
    {
        //parent::__construct($id, $table, $dbConfig);
    }

    public static function addTableSchema(string $table, array $schema) : void {
        self::$schema[$table] = $schema;
    }
}