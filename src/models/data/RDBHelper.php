<?php

declare(strict_types=1);

namespace mmvc\models\data;

use mmvc\models\data\sql\AbstractQueryHelper;
use mmvc\models\data\sql\QueryHelper;
use \PDO;
use PDOStatement;

class RDBHelper extends AbstractDataStorage implements Transactional
{

    const DB_TYPE_MSSQL = 'mssql';
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_PGSQL = 'pgsql';
    const DB_TYPE_SQLITE = 'sqlite';

    /**
     *
     * @var \PDO 
     */
    protected PDO $connection;

    /**
     * Загруженные хелперы для работы с запросами
     * @var array каждый элемент - наследник 
     * \mmvc\models\data\sql\AbstractQueryHelper
     */
    protected static array $queryHelpers;


    /**
     * Создание нового соединения к базе
     * @param array|null $dbConfig
     * @global array $config
     */
    public function __construct(?array $dbConfig = null)
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
     * @return AbstractQueryHelper
     */
    protected function addQueryHelper(string $driverName): sql\AbstractQueryHelper
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
    public function isConnected(): bool
    {
        return isset($this->connection) && $this->connection instanceof \PDO;
    }

    /**
     * @param PDOStatement $statement
     */
    private function dropQueryExecuteException(PDOStatement $statement)
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
     * @param string $sqlQuery
     * @param array|null $values
     * @return PDOStatement в случае успеха
     */
    public function execute(string $sqlQuery, ?array $values = null): ?PDOStatement
    {
        $st = $this->connection->prepare($sqlQuery);
        $res = is_array($values) && count($values) > 0 ? $st->execute($values) : $st->execute();
        if ($res !== false) {
            return $st;
        }
        $this->dropQueryExecuteException($st, $values);
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function getDriverName(): string
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     *
     * @return QueryHelper
     */
    public function getQueryHelper(): QueryHelper
    {
        return self::$queryHelpers[$this->getDriverName()];
    }

    /**
     * @throws \PDOException
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * @throws \PDOException
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * @throws \PDOException
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}
