<?php

namespace app\models\data;

abstract class RDBHelper extends AbstractDataStorage  {
    
     /**
     *
     * @var \PDO 
     */
    protected $connection;
    
    /**
     * Схема данных для таблицы (одна для всех существующих объектов каждой таблицы)
     * schema[tablename] = ['type' => string, 'size' => integer, 'default' => mixed]
     * Заполняется при первом обращении к таблице запросом DESCRIBE $tablename
     * @var array 
     */
    protected static $schema;
    
    // sqlite - diff
     /**
     * Создание нового соединения к базе
     * @global array $config
     * @return boolean
     * @throws \PDOException
     */
    public function __construct($dbConfig = null)
    {
        $db_opt = $dbConfig;
        if ($config === null)
        {
            global $config;
            $db_opt = $config['db'];
        }
        
        $connectionString =  "{$db_opt['driver']}}:host={$db_opt['host']};"
                            . "port=5432;dbname={$db_opt['schema']};";
        
        $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->connection = new \PDO($connectionString, 
                $db_opt['username'],
                $db_opt['password'],
                $opt);
        
        return $this->isConnected();
    }
    
     /**
     * Проверка соединения с базой
     * @return boolean
     */
    public function isConnected()
    {
        return isset($this->connection) && $this->connection instanceof \PDO; 
    }
    
    // mssql sp_help "[SchemaName].[TableName]" 
    // firebird show table "table_name"
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public abstract function parseSchema($table_name);
    
    /**
     * Получение типа данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return string
     */
    protected abstract function getType($type);
    
    /**
     * Получение размера данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return int
     */
    protected abstract function getTypeSize($type);
    
    public function isPropertyExists($table_name, $property_name)
    {
        return isset($this->schema[$table_name][$property_name]);
    }
    
    public function getSchema($table_name)
    {
        if (!isset($this->schema) || !isset($this->schema[$table_name])) {
            return null;
        }

        return $this->schema[$table_name];
    }
    
    /**
     * Выполнение произвольного sql-запроса
     * @param string $sql_query
     * @return mixed
     */
    public function execute($sql_query)
    {
        return $this->connection->query($sql_query);
    }
    
    /** 
     * Получение имени типа из загруженной ранее схемы
     * @param string $table_name
     * @param string $field
     */
    public function getTypeName($table_name, $field)
    {
        if (!isset($this->schema[$table_name]))
            throw new \Exception("Schema for table $table_name is not loaded yet");

        return $this->schema[$table_name][$field]['type'];
    }

}
