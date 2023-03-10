<?php namespace mmvc\models\data\sql;

use mmvc\models\data\StoredObject;
use mmvc\models\data\RDBRecord;
use mmvc\models\data\RDBHelper;

class MysqlQueryHelper extends AbstractQueryHelper
{

    const PROPERTY_ATTRIBUTE_FLAGS = 'flags';

    public function isPrimaryKey($data)
    {
        return isset($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS]) && ($data[MysqlQueryHelper::PROPERTY_ATTRIBUTE_FLAGS] & MYSQLI_PRI_KEY_FLAG);
    }

    public function getPrimaryColumn(&$properties)
    {
        foreach ($this->properties as $key => $data) {
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

        return $query . " LIMIT :limit, :offset ";
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
        foreach ($values as $key => $value) {
            if (strlen($set) > 0) {
                $set .= ', ';
            }
            $value = self::serializeProperty($value, RDBRecord::getTypeName($table, $key));
            $this->addQueryValue($key, $value);
            $set .= "`$key`=:$key";
        }

        $query = "UPDATE $table SET $set ";
        if ($where !== null) {
            $query .= self::addWhere($where);
        }
        return $query;
    }
    
    /**
     * ???????????????? ?????????????? ?????? ???????????????????? ???????????? ?? ????????
     * ???????????????????? ?????? ???????????????????? (?????????? save())
     * @return string ?????????????? ???????????? INSERT INTO $tablename ($columns) VALUES ($values);
     */
    public function buildInsert($table, &$properties)
    {
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
            $value = self::serializeProperty(
                $data[StoredObject::PROPERTY_ATTRIBUTE_VALUE], 
                RDBRecord::getTypeName($table, $key)
            );
            $this->addQueryValue($key, $value);
            $values .= ":$key";
        }

        $q = "INSERT INTO $table ($props) VALUES ($values);";
        return $q;
    }

    /**
     * ???????????????????? ???????????????? ?????????????? ?? ???????????? ?????? ???????????? ?? ????
     * @param mixed $value ???????????????? ??????????????
     * @param string $type ???????????????? ???????? ???????????? ?? ?????????????????? ??????????????????????????
     * @return string ?????????????????? ?????????????????????????? ???????? ????????????
     * @throws Exception ???????????????????????? ???????? ???????????????????????? ?????? ????????????????????
     * ?????? ???????? ?????????????? ?????? set, ???? $variable ???? ????????????
     */
    private function serializeProperty($value, $type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'tinyint':
            case 'integer':
            case 'int':
                return (string) intval($value);
            case 'string':
            case 'enum':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'varchar':
                return self::filterString($value);
            case 'double':
                return (string) floatval($value);
            case 'set':
                if (is_array($value))
                    return "(" . implode(", ", $value) . ")";
                throw new \Exception("Variable 'value' is not array.");
            case 'bit':
                return boolval($value) ? "1" : "0";
            default:
                throw new \Exception("Unknown type $type.");
        }
    }

    public function buildSelect($fields = '*', $from, $where = null)
    {
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
        if ($where !== null) {
            $query .= self::addWhere($where);
        }

        return $query;
    }

    public function buildDelete($table, $where)
    {
        $query = "DELETE FROM $table " . self::addWhere($where);
    }

    public function filterString($value)
    {
        return $value;
    }

    public function getPropertyType($dbPropertyType)
    {
        switch ($dbPropertyType) {
            case 'tinyint':
            case 'integer':
            case 'int':
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
