<?php

namespace app\models\data\sql;

use app\models\BaseModel;
use app\models\data\StoredObject;
use app\models\data\RDBHelper;

abstract class AbstractQueryHelper extends BaseModel {

    /**
     * Имя используемого классом драйвера для генерации запросов
     * @var string 
     */
    protected $driverName;

    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL = 'FULL';

    public function __construct() {
        $this->driverName = $this->findDriverName();
    }

    /**
     * Выделение имени драйвера из имени класса
     * Например, MysqlQueryHelper -> mysql
     * @return string
     */
    private function findDriverName() {
        $matches = [];
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $this->getName(), $matches);
        $ret = $matches[0];

        return lcfirst($ret[0]);
    }

    /**
     * Возвращает используемое хелпером имя драйвера
     * @return string
     */
    public function getDriverName() {
        return $this->driverName;
    }

    public abstract function buildDelete($table, $where);

    public abstract function buildSelect($fields = '*', $from, $where = null);

    public abstract function buildDescribe($table);

    public abstract function buildInsert(&$properties);

    public abstract function buildUpdate($table, $values, $where = null);

    public abstract function addLimit($query, $limit, $offset = 0);

    public abstract function addWhere($where);

    public abstract function addJoin($query, $type, $table, $on);

    /**
     * Фильтрация строки, используемая для работы с БД
     * @param string $value
     * @return string
     */
    public abstract function filterString($value);

}
