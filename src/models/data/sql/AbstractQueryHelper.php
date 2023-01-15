<?php

declare(strict_types=1);

namespace mmvc\models\data\sql;

use mmvc\models\BaseModel;

abstract class AbstractQueryHelper extends BaseModel implements QueryHelper, QueryValuesStore
{

    /**
     * Массив ключ-значение для вставки параметров в PDO
     * @var array 
     */
    private array $queryValues;

    /**
     * Имя используемого классом драйвера для генерации запросов
     * @var string 
     */
    protected string $driverName;

    public function __construct()
    {
        $this->queryValues = [];
        $this->driverName = $this->findDriverName();
    }

    public function getQueryValues(): array
    {
        return $this->queryValues;
    }

    public function clearQueryValues(): void
    {
        $this->queryValues = [];
    }

    public function addQueryValue(string $name, $value): void
    {
        $this->queryValues[":$name"] = $value;
    }

    /**
     * Выделение имени драйвера из имени класса
     * Например, MysqlQueryHelper -> mysql
     * @return string
     */
    private function findDriverName(): string
    {
        $matches = [];
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $this->getName(), $matches);
        $ret = $matches[0];

        return lcfirst($ret[0]);
    }

    /**
     * Возвращает используемое хелпером имя драйвера
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driverName;
    }
    
    /**
     * Соотвествие между типами данных в СУБД и типами данных в PHP
     * Используется при генерации модели через консоль во время
     * обработки схемы таблицы
     * @param string $dbPropertyType Тип данных СУБД
     * @return string Тип данных в PHP
     */
    public abstract function getPropertyType(string $dbPropertyType): string;
}
