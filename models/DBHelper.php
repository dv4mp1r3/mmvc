<?php

namespace app\models;

class DBHelper extends BaseModel
{
    /**
     *
     * @var \mysqli
     */
    public static $connection;
    
    /**
     * Схема данных для таблицы (одна для всех существующих объектов каждой таблицы)
     * schema[tablename] = ['type' => string, 'size' => integer, 'default' => mixed]
     * Заполняется при первом обращении к таблице запросом DESCRIBE $tablename
     * @var array 
     */
    protected static $schema;
    
     /**
     * Создание нового соединения к базе
     * @global array $config
     * @return \mysqli
     * @throws \Exception
     */
    public static function createConnection()
    {
        global $config;
        $db_opt = $config['db'];
        
        self::$connection = new \mysqli($db_opt['host'], 
                $db_opt['username'], 
                $db_opt['password'], 
                $db_opt['schema']);

        if (self::$connection->connect_errno)
        {
            throw new \Exception('mysql error: '. self::$connection->error);
        }
        
        return self::$connection;
    }
    
    /**
     * Проверка соединения с базой
     * @return boolean
     */
    public static function isConnected()
    {
        if (isset(self::$connection) && self::$connection instanceof \mysqli)
        {
            return self::$connection->ping();
        }
        
        return false;
    }
    
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public static function parseSchema($table_name)
    {
        if (!self::isConnected())
            self::createConnection ();
        
        $result = self::$connection->query("DESCRIBE $table_name");
        while ($row = mysqli_fetch_array($result)) {
            self::$schema[$table_name][$row['Field']] = 
                    [
                      'type' => self::getType($row['Type']),
                      'size' => self::getTypeSize($row['Type']),
                      'default' => $row['Default'],
                    ];
        }    
    }
    
    /**
     * Получение типа данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return string
     */
    private static function getType($type)
    {
        return substr($type, 0, strpos($type, '('));
    }
    
    /**
     * Получение размера данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return int
     */
    private static function getTypeSize($type)
    {
        $begin = strpos($type, '(');
        return (int)substr($type,  $begin + 1, strlen($type) - $begin - 2);
    } 
    
    public static function isPropertyExists($table_name, $property_name)
    {      
        return isset(self::$schema[$table_name][$property_name]);
    }
    
    public static function getSchema($table_name)
    {
        return self::$schema[$table_name];
    }
}
