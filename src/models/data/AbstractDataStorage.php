<?php namespace mmvc\models\data;

abstract class AbstractDataStorage extends \mmvc\models\BaseModel
{

    /**
     *
     * @var mixed
     */
    protected \PDO $connection;

    /**
     * @var mixed 
     */
    protected static $schema;

    /**
     * Проверка соединения с базой
     * @return boolean
     */
    public abstract function isConnected();
}
