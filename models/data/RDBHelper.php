<?php

namespace app\models\data;

use \PDO;

/**
 * TODO 
 * вынести конфиг в класс-приложение чтобы убрать использование глобальной переменной
 * 
 */
class RDBHelper extends AbstractDataStorage {

    const DB_TYPE_MSSQL = 'mssql';
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_PGSQL = 'pgsql';
    const DB_TYPE_SQLITE = 'sqlite';
    /**
     *
     * @var \PDO 
     */
    protected $connection;
    
    /**
     * Загруженные хелперы для работы с запросами
     * @var array каждый элемент - наследник 
     * \app\models\data\sql\AbstractQueryHelper
     */
    protected static $queryHelpers;

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
        $this->addQueryHelper($this->getDriverName());
        return $this->isConnected();
    }
    
    /**
     * Добавление хелпера запросов для driverName
     * Имя класс помещается в RDBHelper::$queryHelpers
     * @param string $driverName
     * @return \app\models\data\sql\AbstractQueryHelper 
     */
    protected function addQueryHelper($driverName)
    {
        $classname = 'app\\models\\data\\sql\\'.ucfirst($driverName).'QueryHelper';
        $obj = new $classname();
        self::$queryHelpers[$driverName] = $obj;
        
        return $obj;
    }

    /**
     * Проверка соединения с базой
     * @return boolean
     */
    public function isConnected() {
        return isset($this->connection) && $this->connection instanceof \PDO;
    }

    private function dropQueryExecuteException($statement) {
        $errCode = $statement->errorCode();
        $errInfo = $statement->errorInfo();
        throw new \PDOException("PDO error (code $errCode) $errInfo.");
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
    
    public function getDriverName()
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    } 
    
    /**
     * 
     * @return app\models\data\sql\AbstractQueryHelper
     */
    public function getQueryHelper()
    {
        return self::$queryHelpers[$this->getDriverName()];
    }
}
