<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

use mmvc\models\data\StoredObject;
use mmvc\models\data\RDBRecord;
use mmvc\models\data\RDBHelper;

class MysqlQueryHelper extends AbstractQueryHelper
{

    const PROPERTY_ATTRIBUTE_FLAGS = 'flags';

    const DEFAULT_DATETIME_FORMAT = '%d-%m-%Y %H:%i:%s';
    const DEFAULT_TIME_FORMAT = '%H:%i:%s';
    const DEFAULT_DATE_FORMAT = '%d-%m-%Y';

    public function isPrimaryKey($data)
    {
        return isset($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS]) && ($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS] & MYSQLI_PRI_KEY_FLAG);
    }

    public function getPrimaryColumn(array $properties)
    {
        foreach ($properties as $key => $data) {
            if ($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS] & MYSQLI_PRI_KEY_FLAG) {
                return $key;
            }
        }
    }

    public function addJoin($query, $type, $table, $on)
    {
        return $query . " $type JOIN $table ON $on";
    }

    public function addLimit($query, $limit, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);

        $this->addQueryValue('limit', $limit);
        $this->addQueryValue('offset', $offset);

        return $query . " LIMIT :offset, :limit ";
    }

    public function addWhere($where, $values = null)
    {
        if (is_array($values))
        {
            foreach ($values as $key => &$value)
            {
                $this->addQueryValue($key, $value);
            }
        }

        return " WHERE " . $where;
    }

    public function buildDescribe($table)
    {
        return "DESCRIBE $table";
    }

    public function buildUpdate($table, $values, $where = null)
    {
        $set = '';
        $dateTypes = ['datetime', 'time', 'date'];
        foreach ($values as $key => $value) {
            if (strlen($set) > 0) {
                $set .= ', ';
            }
            $type = RDBRecord::getTypeName($table, $key);
            $serializedValue = self::serializeProperty($value, $type, $key);
            if (in_array($type, $dateTypes)) {
                $set .= "`$key`= $serializedValue";
            }
            else {
                $set .= "`$key`=:$key";
            }
            $this->addQueryValue($key, $value);

        }

        $query = "UPDATE $table SET $set ";
        if ($where !== null) {
            $query .= self::addWhere($where);
        }
        return $query;
    }

    /**
     * Создание запроса для добавления записи в базу
     * Вызывается при сохранении (метод save())
     * @param string $table
     * @param array $properties
     * @return string готовый запрос INSERT INTO $tablename ($columns) VALUES ($values);
     * @throws \Exception
     */
    public function buildInsert($table, &$properties): string
    {
        $props = '';
        $values = '';
        $delemiter = ', ';
        $dateTypes = ['datetime', 'time', 'date'];
        foreach ($properties as $key => $data) {
            if (!isset($data[StoredObject::PROPERTY_ATTRIBUTE_VALUE]) || self::isPrimaryKey($data)) {
                continue;
            }
            if (strlen($props) > 0) {
                $props .= $delemiter;
            }
            $props .= "`$key`";

            if (strlen($values) > 0) {
                $values .= $delemiter;
            }
            $properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = false;
            $type = RDBRecord::getTypeName($table, $key);
            $value = self::serializeProperty(
                $data[StoredObject::PROPERTY_ATTRIBUTE_VALUE],
                $type,
                $key
            );
            if (in_array($type, $dateTypes)) {
                $values .= "$value";
                $this->addQueryValue($key, $data[StoredObject::PROPERTY_ATTRIBUTE_VALUE]);
            } else {
                $values .= ":$key";
                $this->addQueryValue($key, $value);
            }
        }

        $q = "INSERT INTO $table ($props) VALUES ($values);";
        return $q;
    }

    /**
     * Приведение свойства объекта к строке для записи в БД
     * @param mixed $value значение объекта
     * @param string $type название типа данных в строковом представлении
     * @return string строковое представление типа данных
     * @throws Exception генерируется если передаваемый тип неизвестен
     * Или если передан тип set, но $variable не массив
     */
    private function serializeProperty($value, $type, $key)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'tinyint':
            case 'integer':
            case 'int':
            case 'mediumint':
                return (string) intval($value);
            case 'string':
            case 'enum':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'varchar':
                return $value;
            case 'double':
            case 'float':
                return (string) floatval($value);
            case 'set':
                if (is_array($value)) {
                    return "(" . implode(", ", $value) . ")";
                }
                throw new \Exception("Variable 'value' is not array.");
            case 'datetime':
                return 'STR_TO_DATE(:'.$key.', \''.self::DEFAULT_DATETIME_FORMAT.'\' )';
            case 'date':
                return 'STR_TO_DATE(:'.$key.', \''.self::DEFAULT_DATE_FORMAT.'\' )';
            case 'time':
                return 'STR_TO_DATE(:'.$key.', \''.self::DEFAULT_TIME_FORMAT.'\' )';
            case 'bit':
                return boolval($value) ? "1" : "0";
            default:
                throw new \Exception("Unknown type $type.");
        }
    }

    public function buildSelect($fields = '*', $from, $where = null, $values = null)
    {
        if (!is_array($fields)) {
            $query = "SELECT * ";
        } else {
            $fields = implode(", ", $fields);
            $query = "SELECT $fields ";
        }

        $query .= " FROM $from ";
        if ($where !== null) {
            $query .= self::addWhere($where, $values);
        }

        return $query;
    }

    public function buildDelete($table, $where, $values = null)
    {
        return "DELETE FROM $table " . self::addWhere($where, $values);
    }

    public function getPropertyType($dbPropertyType)
    {
        switch ($dbPropertyType) {
            case 'tinyint':
            case 'integer':
            case 'int':
            case 'mediumint':
                return 'integer';
            case 'string':
            case 'tinytext':
            case 'mediumtext':
            case 'varchar':
            case 'datetime':
            case 'date':
            case 'time':
                return 'string';
            case 'double':
            case 'float':
                return 'double';
            case 'enum':
            case 'set':
                return 'array';
            case 'bit':
            case 'bool':
                return 'boolean';
            default:
                throw new \Exception("Unknown type $dbPropertyType.");
        }
    }
}
