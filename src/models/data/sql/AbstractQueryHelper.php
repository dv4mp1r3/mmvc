<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

use mmvc\models\BaseModel;
use mmvc\models\data\StoredObject;
use mmvc\models\data\RDBHelper;

abstract class AbstractQueryHelper extends BaseModel
{

    /**
     * Массив ключ-значение для вставки параметров в PDO
     * @var array 
     */
    private array $queryValues;

    /**
     * Имя используемого классом драйвера для генерации запросов
     * @var string 
     */
    protected string $driverName;

    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL = 'FULL';

    public function __construct()
    {
        $this->queryValues = [];
        $this->driverName = $this->findDriverName();
    }

    public function getQueryValues()
    {
        return $this->queryValues;
    }

    public function clearQueryValues()
    {
        $this->queryValues = [];
    }

    public function addQueryValue($name, $value)
    {
        $this->queryValues[":$name"] = $value;
    }

    /**
     * Выделение имени драйвера из имени класса
     * Например, MysqlQueryHelper -> mysql
     * @return string
     */
    private function findDriverName()
    {
        $matches = [];
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $this->getName(), $matches);
        $ret = $matches[0];

        return lcfirst($ret[0]);
    }

    /**
     * Возвращает используемое хелпером имя драйвера
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    public abstract function buildDelete($table, $where);

    public abstract function buildSelect($fields = '*', $from, $where = null);

    public abstract function buildDescribe($table);

    public abstract function buildInsert($table, &$properties);

    public abstract function buildUpdate($table, $values, $where = null);

    public abstract function addLimit($query, $limit, $offset = 0);

    public abstract function addWhere($where, $values = null);

    public abstract function addJoin($query, $type, $table, $on);

    /**
     * Фильтрация строки, используемая для работы с БД
     * @param string $value
     * @return string
     */
    public abstract function filterString($value);
    
    /**
     * Соотвествие между типами данных в СУБД и типами данных в PHP
     * Используется при генерации модели через консоль во время
     * обработки схемы таблицы
     * @param string $dbPropertyType Тип данных СУБД
     * @return string Тип данных в PHP
     */
    public abstract function getPropertyType($dbPropertyType);
}
