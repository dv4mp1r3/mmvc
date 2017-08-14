<?php namespace mmvc\models\data;

use \PDO;

/**
 * TODO 
 * вынести конфиг в класс-приложение чтобы убрать использование глобальной переменной
 * 
 */
class RDBHelper extends AbstractDataStorage
{

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
     * \mmvc\models\data\sql\AbstractQueryHelper
     */
    protected static $queryHelpers;

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

        if ($db_opt['driver'] === RDBHelper::DB_TYPE_MYSQL) {
            $opt[PDO::MYSQL_ATTR_DIRECT_QUERY] = 0;
        }

        $this->connection = new \PDO($connectionString, $db_opt['username'], $db_opt['password'], $opt);
        $this->addQueryHelper($this->getDriverName());
        return $this->isConnected();
    }

    /**
     * Добавление хелпера запросов для driverName
     * Имя класс помещается в RDBHelper::$queryHelpers
     * @param string $driverName
     * @return \mmvc\models\data\sql\AbstractQueryHelper 
     */
    protected function addQueryHelper($driverName)
    {
        $classname = 'mmvc\\models\\data\\sql\\' . ucfirst($driverName) . 'QueryHelper';
        $obj = new $classname();
        self::$queryHelpers[$driverName] = $obj;

        return $obj;
    }

    /**
     * Проверка соединения с базой
     * @return boolean
     */
    public function isConnected()
    {
        return isset($this->connection) && $this->connection instanceof \PDO;
    }

    private function dropQueryExecuteException($statement, $statementValues = null)
    {
        $errCode = $statement->errorCode();
        $errInfo = $statement->errorInfo();
        if (is_array($errInfo)) {
            $errInfo = implode(',', $errInfo);
        }
        throw new \PDOException("PDO error (code $errCode) $errInfo.");
    }

    /**
     * Выполнение произвольного sql-запроса
     * @param string $sql_query
     * @param array $values
     * @throws \PDOException если не удалось успешно выполнить запрос
     * @return \PDOStatement в случае успеха
     */
    public function execute($sql_query, $values = null)
    {
        $st = $this->connection->prepare($sql_query);
        $res = is_array($values) && count($values) > 0 ? $st->execute($values) : $st->execute();
        if ($res !== false) {
            return $st;
        }
        $this->dropQueryExecuteException($st, $values);
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function getDriverName()
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * 
     * @return mmvc\models\data\sql\AbstractQueryHelper
     */
    public function getQueryHelper()
    {
        return self::$queryHelpers[$this->getDriverName()];
    }
}
