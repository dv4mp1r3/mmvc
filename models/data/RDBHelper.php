<?php

namespace app\models\data;

use \PDO;

class RDBHelper extends AbstractDataStorage {

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
    public function __construct($dbConfig = null) {
        $db_opt = $dbConfig;
        if ($dbConfig === null) {
            global $config;
            $db_opt = $config['db'];
        }

        $connectionString = "{$db_opt['driver']}:host={$db_opt['host']};"
                . "dbname={$db_opt['schema']};";

        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ];

        $this->connection = new \PDO($connectionString, $db_opt['username'], $db_opt['password'], $opt);

        return $this->isConnected();
    }

    /**
     * Проверка соединения с базой
     * @return boolean
     */
    public function isConnected() {
        return isset($this->connection) && $this->connection instanceof \PDO;
    }

    // mssql sp_help "[SchemaName].[TableName]" 
    // firebird show table "table_name"
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public function parseSchema($table_name) {
        $query = "DESCRIBE $table_name";
        $st = $this->connection->prepare($query);

        if (!($st instanceof \PDOStatement)) {
            $this->dropQueryExecuteException($this->connection);
        }

        if (!$st->execute()) {
            $this->dropQueryExecuteException($st);
        }
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            self::$schema[$table_name][$row['Field']] = [
                'type' => self::getType($row['Type']),
                'size' => self::getTypeSize($row['Type']),
                'default' => $row['Default'],
            ];
        }
    }

    private function dropQueryExecuteException($statement) {
        $errCode = $statement->errorCode();
        $errInfo = $statement->errorInfo();
        throw new \PDOException("PDO error (code $errCode) $errInfo.");
    }

    /**
     * Получение типа данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return string
     */
    protected function getType($type) {
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
    protected function getTypeSize($type) {
        $begin = strpos($type, '(');
        return (int) substr($type, $begin + 1, strlen($type) - $begin - 2);
    }

    public static function isPropertyExists($table_name, $property_name) {
        return isset(self::$schema[$table_name][$property_name]);
    }

    public static function getSchema($table_name) {
        if (self::isSchemaExists($table_name)) {
            return null;
        }

        return self::$schema[$table_name];
    }

    public static function isSchemaExists($table_name) {
        return !empty(self::$schema) || !empty(self::$schema[$table_name]);
    }

    /**
     * Выполнение произвольного sql-запроса
     * @param string $sql_query
     * @throws \PDOException если не удалось успешно выполнить запрос
     * @return \PDOStatement в случае успеха или null в противном случае
     */
    public function execute($sql_query) {
        $st = $this->connection->prepare($sql_query);
        $res = $st->execute();
        if ($res === true) {
            return $st;
        }

        $this->dropQueryExecuteException($st);
        return null;
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Получение имени типа из загруженной ранее схемы
     * @param string $table_name
     * @param string $field
     */
    public static function getTypeName($table_name, $field) {
        if (!isset(self::$schema[$table_name])) {
            throw new \Exception("Schema for table $table_name is not loaded yet");
        }

        return self::$schema[$table_name][$field]['type'];
    }

}
