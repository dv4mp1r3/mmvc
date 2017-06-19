<?php

namespace app\models\data;

use app\models\BaseModel;
/**
 * Класс для генерации шаблонов запросов в зависимости от выбранной СУБД
 * 
 * TODO перенести сюда сбор всех запросов из RDBTable
 */
class QueryHelper extends BaseModel {
    
    public static function correctSelect($driver, $query)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                throw new \Exception('not supported yet');
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return $query;
        }
    }
    
    public static function correctUpdate($driver, $query)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                throw new \Exception('not supported yet');
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return $query;
        }
    }
    
    public static function correctInsert($driver, $query)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                throw new \Exception('not supported yet');
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return $query;
        }
    }
    
    public static function correctJoin($driver, $query)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                throw new \Exception('not supported yet');
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return $query;
        }
    }
    
    public static function correctLimit($driver, $query)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                throw new \Exception('not supported yet');
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return $query;
        }
    }
    
    public static function buildDescribe($driver, $table)
    {
        switch($driver)
        {
            case RDBHelper::DB_TYPE_MSSQL:
                return "sp_help $table";
            case RDBHelper::DB_TYPE_MYSQL:
            case RDBHelper::DB_TYPE_PGSQL:
            case RDBHelper::DB_TYPE_SQLITE:
                return "DESCRIBE $table";
        }
    }
}
