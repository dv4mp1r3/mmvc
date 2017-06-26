<?php

namespace app\models\data\sql;

use app\models\data\StoredObject;
use app\models\data\RDBRecord;
use app\models\data\RDBHelper;

class MysqlQueryHelper extends AbstractQueryHelper {
    
    const PROPERTY_ATTRIBUTE_FLAGS = 'flags';
    
    private static function isPrimaryKey($data)
    {
        return isset($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS]) 
        && ($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS] & MYSQLI_PRI_KEY_FLAG);
    }
    
    public static function getPrimaryColumn(&$properties)
    {
        foreach ($this->properties as $key => $data) {
            if ($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS] & MYSQLI_PRI_KEY_FLAG) {
                return $key;
            }
        }
    }
    
    public static function addJoin($query, $type, $table, $on) {
        return $query." $type JOIN $table ON $on";
    }

    public static function addLimit($query, $limit, $offset = 0) {
        $limit = intval($limit);
        $offset = intval($offset);
        
        return $query." LIMIT $offset, $limit ";
    }

    public static function addWhere($where) {
        return " WHERE ".$where;
    }

    public static function buildDescribe($table) {
        return "DESCRIBE $table";
    }

    public static function buildInsert(&$properties) {
        
    }

    public static function buildUpdate($table, $values, $where = null) {
        $set = '';
        foreach ($values as $key => $value) {
            if (strlen($set) > 0) {
                $set .= ', ';
            }
            $value = self::serializeProperty($value, RDBRecord::getTypeName($table, $key));
            $set .= "`$key`=$value";
        }
        
        $query = "UPDATE $table SET $set ";
        if ($where !== null)
        {
            $query .= self::addWhere($where);
        }
        return $query;
    }
    
    /**
     * Создание запроса для добавления записи в базу
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос INSERT INTO $tablename ($columns) VALUES ($values);
     */
    public static function buildInsertQuery($table, &$properties) {
        $props = '';
        $values = '';
        $delemiter = ', ';
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
            $value = self::serializeProperty($data[StoredObject::PROPERTY_ATTRIBUTE_VALUE], 
                    RDBRecord::getTypeName($table, $key)
                    );
            $values .= "'" . str_replace("'", "", $value) . "'";
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
    private static function serializeProperty($value, $type) {
        $type = strtolower($type);
        switch ($type) {
            case 'integer':
            case 'int':
                return (string) intval($value);
            case 'string':
            case 'enum':
            case 'tinytext':
            case 'mediumtext':
            case 'varchar':
                return "'" . self::filterString($value) . "'";
            case 'double':
                return (string) floatval($value);
            case 'set':
                if (is_array($value))
                    return "(" . implode(", ", $value) . ")";
                throw new Exception("Variable 'value' is not array.");
            case 'bit':
                return boolval($value) ? "1" : "0";
            default:
                throw new \Exception("Unknown type $type.");
        }
    }
    
    /**
     * Создание запроса для обновления данных для существующей записи
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос UPDATE $table_name SET (values) WHERE id=$id;
     * @throws Exception выбрасывается, если у объекта нет измененных свойств
     */
    public static function buildUpdateQuery($table, &$properties) {
        $values = '';
        $new_values = 0;
        foreach ($this->properties as $key => $data) {
            if ($properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] == true || self::isPrimaryKey($data)) {
                continue;
            }

            if (strlen($values) > 0) {
                $values .= ', ';
            }
            $value = $this->serializeProperty(
                    $data[StoredObject::PROPERTY_ATTRIBUTE_VALUE], 
                    $data[RDBRecord::PROPERTY_ATTRIBUTE_TYPE]
                    );
            $values .= "`$key`=$value";

            $properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = false;
            $new_values++;
        }
        if ($new_values === 0) {
            throw new \Exception('Model has no changed properties');
        }    
        $id = $properties['id']['value'];
        $q = "UPDATE $table SET $values WHERE `id`='$id'";
        return $q;
    }

    public static function buildSelect($fields = '*', $from, $where = null) {
        $query = '';
        if (!is_array($fields)) {
            $query = "SELECT * ";
        } else {
            foreach ($fields as &$field) {
                $field = self::filterString($field);
            }
            $fields = implode(", ", $fields);
            $query = "SELECT $fields ";
        }
        
        $query .= " FROM $from ";
        if ($where !== null)
        {
            $query .= self::addWhere($where);
        }
        
        return $query;
    }

    public static function buildDelete($table, $where) {
        $query = "DELETE FROM $table ".self::addWhere($where);
    }

    public static function filterString($value) {
        return $value;
    }

}