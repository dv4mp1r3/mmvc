<?php

namespace app\models\data\sql;

use app\models\BaseModel;
use app\models\data\StoredObject;
use app\models\data\RDBHelper;

class AbstractQueryHelper extends BaseModel {

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

    public static function buildDelete($table, $where) {
        throw new \Exception('Not implemented yet');
    }

    public static function buildSelect($fields = '*', $from, $where = null) {
        throw new \Exception('Not implemented yet');
    }

    public static function buildDescribe($table) {
        throw new \Exception('Not implemented yet');
    }

    public static function buildInsert(&$properties) {
        throw new \Exception('Not implemented yet');
    }

    public static function buildUpdate($table, $values, $where = null) {
        throw new \Exception('Not implemented yet');
    }

    public static function addLimit($query, $limit, $offset = 0) {
        throw new \Exception('Not implemented yet');
    }

    public static function addWhere($where) {
        throw new \Exception('Not implemented yet');
    }

    public static function addJoin($query, $type, $table, $on) {
        throw new \Exception('Not implemented yet');
    }

    /**
     * Фильтрация строки, используемая для работы с БД
     * @param string $value
     * @return string
     */
    public static function filterString($value) {
        throw new \Exception('Not implemented yet');
    }

}
