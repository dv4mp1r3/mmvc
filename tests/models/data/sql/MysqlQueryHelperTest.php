<?php

declare(strict_types=1);

namespace tests\models\data\sql;

use mmvc\models\data\sql\MysqlQueryHelper;
use tests\models\data\RDBRecordMock;
use PHPUnit\Framework\TestCase;

class MysqlQueryHelperTest extends TestCase
{
    const TABLE_NAME = 'test';
    const FIELDS_ALL = '*';
    const FIELDS = ['field1', 'field2'];

    private MysqlQueryHelper $helper;

    private static function fieldsAsString(): string {
        return implode(", ", self::FIELDS);
    }

    private function removeSpaces(string $str): string {
        return str_replace(' ', '', $str);
    }

    private function fieldsToFieldVal() : array {
        $result = [];
        foreach (self::FIELDS as $field) {
            $result[$field] = 1;
        }
        return $result;
    }

    public function setUp(): void
    {
        $this->helper = new MysqlQueryHelper();
    }

    public function testBuildDelete(): void {
        $query = $this->helper->buildDelete(self::TABLE_NAME, "1=:val", ['val' => 1]);
        $this->assertEquals(
            $this->removeSpaces("DELETE FROM ".self::TABLE_NAME." WHERE 1=:val"),
            $this->removeSpaces($query)
        );
        $this->assertEquals([':val' => 1], $this->helper->getQueryValues());
    }

    public function testBuildDescribe(): void {
        $this->assertEquals(
            $this->removeSpaces("DESCRIBE ".self::TABLE_NAME),
            $this->removeSpaces($this->helper->buildDescribe(self::TABLE_NAME))
        );
    }

    public function testBuildSelectAll(): void {
        $query = $this->helper->buildSelect(self::FIELDS_ALL, self::TABLE_NAME, '1=1');
        $this->assertEquals(
            $this->removeSpaces("SELECT *  FROM  ".self::TABLE_NAME."  WHERE 1=1"),
            $this->removeSpaces($query)
        );

        $query = $this->helper->buildSelect(
            self::FIELDS_ALL,
            self::TABLE_NAME,
            "1=:val",
            ['val' => 1]
        );
        $this->assertEquals(
            $this->removeSpaces("SELECT * FROM ".self::TABLE_NAME." WHERE 1=:val"),
            $this->removeSpaces($query)
        );
        $this->assertEquals([':val' => 1], $this->helper->getQueryValues());

        $query = $this->helper->buildSelect(self::FIELDS, self::TABLE_NAME);
        $this->assertEquals(
            $this->removeSpaces("SELECT ".self::fieldsAsString()." FROM ".self::TABLE_NAME." "),
            $this->removeSpaces($query)
        );
    }

    public function testBuildUpdate(): void {
        RDBRecordMock::addTableSchema(
            self::TABLE_NAME,
            [
                'field1'=>['type'=>'int', 'size'=>'11', 'default'=>'0'],
                'field2'=>['type'=>'int', 'size'=>'11', 'default'=>'0'],
            ]
        );
        $query = $this->helper->buildUpdate(self::TABLE_NAME, $this->fieldsToFieldVal());
        $this->assertEquals(
            $this->removeSpaces("UPDATE test SET `field1`=:field1, `field2`=:field2 "),
            $this->removeSpaces($query)
        );
        $this->assertEquals([':field1' => 1, ':field2' => 1,], $this->helper->getQueryValues());
    }


}