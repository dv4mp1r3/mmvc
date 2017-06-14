<?php

namespace app\models\data;

abstract class AbstractDataStorage {
    /**
     *
     * @var mixed
     */
    protected $connection;
    
    /**
     * @var mixed 
     */
    protected $schema;
    
    public abstract function __construct();
    
     /**
     * Проверка соединения с базой
     * @return boolean
     */
    public abstract function isConnected();
    
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public abstract function parseSchema($table_name);
       
}
