<?php

declare(strict_types=1);

namespace tests\models\data\sql;

use mmvc\models\data\sql\MysqlQueryHelper;
use mmvc\models\data\StoredObject;
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

    private function addTestSchema(array $additional = []): void {
        RDBRecordMock::addTableSchema(
            self::TABLE_NAME,
            array_merge([
                'field1'=>['type'=>'int', 'size'=>'11', 'default'=>'0'],
                'field2'=>['type'=>'int', 'size'=>'11', 'default'=>'0'],
            ],
            $additional)
        );
    }

    private function genFlagProreprties(): array {
        return [
            'id' => [
                MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS => 0 | MYSQLI_PRI_KEY_FLAG
            ],
            'field1' => [
                MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS => 0,
            ],
        ];
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
        $this->addTestSchema();
        $query = $this->helper->buildUpdate(self::TABLE_NAME, $this->fieldsToFieldVal());
        $this->assertEquals(
            $this->removeSpaces("UPDATE test SET `field1`=:field1, `field2`=:field2 "),
            $this->removeSpaces($query)
        );
        $this->assertEquals([':field1' => 1, ':field2' => 1,], $this->helper->getQueryValues());
    }

    public function testAddLimit(): void {
        $this->assertEquals(' LIMIT :offset, :limit ', $this->helper->addLimit('', 1000));
        $this->assertEquals([':limit' => 1000, ':offset' => 0], $this->helper->getQueryValues());

        $this->helper->clearQueryValues();
        $this->assertEquals(' LIMIT :offset, :limit ', $this->helper->addLimit('', 1000, 1));
        $this->assertEquals([':limit' => 1000, ':offset' => 1], $this->helper->getQueryValues());
    }

    public function testAddWhere(): void {
        $this->assertEquals(' WHERE 1=:val', $this->helper->addWhere('1=:val', ['val' => 1]));
        $this->assertEquals([':val'=>1], $this->helper->getQueryValues());
    }

    public function testBuildInsert(): void {
        $this->addTestSchema([
            'field3'=> ['type'=>'datetime', 'size'=>'8', 'default'=>null],
            'field4'=> ['type'=>'text', 'size'=>'1', 'default'=>null],
            'field5'=> ['type'=>'double', 'size'=>'1', 'default'=>null],
            'field6'=> ['type'=>'bit', 'size'=>'1', 'default'=>null],
            'field7'=> ['type'=>'set', 'size'=>'1', 'default'=>null],
        ]);
        $date = date(str_replace('%', '', MysqlQueryHelper::DEFAULT_DATETIME_FORMAT));
        $properties = [
            'field1' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => 1,
            ],
            'field2' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => 10,
            ],
            'field3' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => $date,
            ],
            'field4' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => 'hello, world',
            ],
            'field5' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => 1.1,
            ],
            'field6' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => true,
            ],
            'field7' => [
                StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY => true,
                StoredObject::PROPERTY_ATTRIBUTE_VALUE => ['set1', 'set2'],
            ],
        ];
        $props = '`'.implode('`, `', array_keys($properties)).'`';
        $vals = ":field1, :field2, STR_TO_DATE(:field3, '%d-%m-%Y %H:%i:%s' ), :field4, :field5, :field6, :field7";
        $query = $this->helper->buildInsert(self::TABLE_NAME, $properties);
        $this->assertEquals(
            'INSERT INTO '.self::TABLE_NAME." ($props) VALUES ($vals);",
            $query
        );

        $allPropsAreNotDirty = true;
        foreach ($properties as $key => $val) {
            if ($val[StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY]) {
                $allPropsAreNotDirty = false;
                break;
            }
        }
        $this->assertEquals(true, $allPropsAreNotDirty);

        $this->assertEquals(
            [
                ':field1' => "1",
                ':field2' => "10",
                ':field3' => $date,
                ':field4' => 'hello, world',
                ':field5' => "1.1",
                ':field6' => "1",
                ':field7' => "(set1, set2)",
            ],
            $this->helper->getQueryValues()
        );
    }

    public function testAddJoin() : void {
        $join = $this->helper->addJoin('', 'LEFT', self::TABLE_NAME, 'field=field');
        $this->assertEquals(' LEFT JOIN '.self::TABLE_NAME.' ON field=field', $join);
    }

    public function testIsPrimaryKey() : void {
        $properties = $this->genFlagProreprties();
        $this->assertEquals(true, $this->helper->isPrimaryKey($properties['id']));
        $this->assertEquals(false, $this->helper->isPrimaryKey($properties['field1']));
    }

    public function testGetPrimaryColumn() : void {
        $this->assertEquals('id', $this->helper->getPrimaryColumn($this->genFlagProreprties()));
    }

}