<?php

declare(strict_types=1);

namespace mmvc\models\data;

use mmvc\models\data\RDBRecord;

class RDBSchemaRecord
{
    const PROPERTY_ATTRIBUTE_SCHEMA = 'schema';
    const PROPERTY_ATTRIBUTE_TYPE = 'type';

    /**
     * Схема данных для таблицы (одна для всех существующих объектов каждой таблицы)
     * schema[tablename] = ['type' => string, 'size' => integer, 'default' => mixed]
     * Заполняется при первом обращении к таблице запросом DESCRIBE $tablename
     * @var array
     * @see RDBRecord::parseSchema
     */
    protected static array $schema;

    /**
     * Получение типа данных PHP для свойства по его имени
     * с учетом схемы данных СУБД
     * @param string $propertyName
     * @return string
     * @throws \Exception выбрасывается если нет схемы
     */
    public function getPropertyType(string $propertyName): string
    {
        $table = $this->objectName;
        if (!self::isSchemaExists($table)) {
            throw new \Exception('Empty schema for table ' . $table);
        }
        $propType = self::$schema[$table][$propertyName][self::PROPERTY_ATTRIBUTE_TYPE];

        return $this->queryHelper->getPropertyType($propType);
    }

    /**
     * Получение имени типа из загруженной ранее схемы
     * @param string $tableName
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    public static function getTypeName(string $tableName, string $field): string
    {
        if (!isset(self::$schema[$tableName])) {
            throw new \Exception("Schema for table $tableName is not loaded yet");
        }

        return self::$schema[$tableName][$field]['type'];
    }

    public static function isSchemaExists($tableName): bool
    {
        return empty(self::$schema) ? false : !empty(self::$schema[$tableName]);
    }

    /**
     * Получение типа данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return string
     */
    protected function getType(string $type): string
    {
        $pos = strpos($type, '(');
        if ($pos > 0) {
            return substr($type, 0, $pos);
        }
        return $type;
    }

    /**
     * Получение размера данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return int
     */
    protected function getTypeSize(string $type): int
    {
        $begin = strpos($type, '(');
        return (int)substr($type, $begin + 1, strlen($type) - $begin - 2);
    }

    public static function isPropertyExists($tableName, $propertyName): bool
    {
        return isset(self::$schema[$tableName][$propertyName]);
    }

    public static function getSchema($tableName): ?array
    {
        if (self::isSchemaExists($tableName)) {
            return self::$schema[$tableName];
        }

        return null;
    }

    public function addColumn(string $table, array $row) : void
    {
        if (!$this->checkRow($row)) {
            return;
        }
        self::$schema[$table][$row['Field']] = [
            'type' => self::getType($row['Type']),
            'size' => self::getTypeSize($row['Type']),
            'default' => $row['Default'],
        ];
    }

    private function checkRow($row): bool
    {
        $keys = ['Field', 'Type', 'Default'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                return false;
            }
        }
        return true;
    }
}