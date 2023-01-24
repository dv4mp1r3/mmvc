<?php

declare(strict_types=1);

namespace mmvc\models\data;

use mmvc\models\data\sql\QueryHelper;
use \PDO;
use \PDOStatement;
use mmvc\models\data\sql\AbstractQueryHelper;

/**
 * @todo реализация one2many / many2many отношений
 * Представление выборки данных из таблицы или таблиц как объекта со свойствами
 */
class RDBRecord extends StoredObject
{


    /**
     * Инстанс объекта для работы с БД
     * @var \mmvc\models\data\RDBHelper
     */
    protected RDBHelper $dbHelper;

    /**
     * Хелпер для генерации запросов в СУБД
     * @var AbstractQueryHelper
     */
    protected $queryHelper;

    /*
     * текущий сгенерированный запрос через методы select, where, update, join     
     * затирается после выполнения запроса
     * @var string
     */
    private string $sqlQuery;

    /* присутствует ли join с таблицей внутри запроса $sql_query
     * устанавливается в true при вызове join
     * затирается после выполнения запроса
     * @var boolean
     */
    private bool $sqlIsJoin;

    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param int|null $id PK записи для выгрузки существующий (опционально)
     * @param string|null $table
     * @param array|null $dbConfig
     */
    public function __construct(?int $id = null, ?string $table = null, ?array $dbConfig = null)
    {

        parent::__construct();

        if ($table !== null) {
            $this->objectName = $table;
        }

        $this->dbHelper = new RDBHelper($dbConfig);
        $this->queryHelper = $this->dbHelper->getQueryHelper();

        $this->isNew = ($id === null);
        // Если не новый объект (уже находится в БД)
        if (!$this->isNew) {
            $this->initStored($id);
        }
        $this->sqlQuery = "";
        $this->sqlIsJoin = false;
        $this->firstLoad = false;
    }

    /**
     *
     * @return QueryHelper
     */
    protected function getQueryHelper(): QueryHelper
    {
        return $this->dbHelper->getQueryHelper();
    }

    /**
     * Загрузка существующего в БД объекта по первичному ключу
     * @param integer $id
     */
    protected function initStored(int $id)
    {
        if (!RDBSchemaRecord::isSchemaExists($this->objectName)) {
            $this->parseSchema($this->objectName);
        }

        $sql = $this->queryHelper->buildSelect($this->objectName, ['*'], "id=$id");
        $st = $this->dbHelper->execute($sql);

        $this->fillProperties((array)$st->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Заполнение свойств объекта из результатов запроса
     * @param array $props результат выполнения запроса
     * @param boolean $ignore_schema нужно ли игнорировать схему при заполнении свойств
     * Например, при выполнении join и помещении результатов в один объект
     * @see mmvc\models\data\RDBRecord::execute()
     */
    protected function fillProperties(array $props, $ignore_schema = false)
    {
        foreach ($props as $key => $value) {
            if (RDBSchemaRecord::isPropertyExists($this->objectName, $key) || $ignore_schema === true) {
                $this->__set($key, $value);
                $this->properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = false;
            }
        }
    }

    /**
     * Перегрузка метода присваивания значения свойству
     * При изменении значения id ему не выставляется свойство is_dirty в true
     * @param string $name
     * @param mixed $value
     * @see mmvc\models\data\StoredObject::__set()
     */
    public function __set(string $name, $value)
    {
        $this->properties[$name][StoredObject::PROPERTY_ATTRIBUTE_VALUE] = $value;
        if (!$this->firstLoad && $name !== 'id') {
            $this->properties[$name][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = true;
        }
    }

    /**
     * Получение схемы таблицы для записи
     * @return array
     */
    public function getObjectSchema(): ?array
    {
        return RDBSchemaRecord::getSchema($this->objectName);
    }

    /**
     * Сохранение модели как записи в БД
     * Для созданной модели генерируется запрос INSERT INTO
     * А также свойство is_new устанавливается в false
     * Для найденной UPDATE
     * Свойство is_dirty для каждого атрибута ставится в false на этапе генерации запроса
     * @throws \Exception
     */
    public function save()
    {
        $query = '';
        if ($this->isNew) {
            $this->parseSchema($this->objectName);
            $query = $this->queryHelper->buildInsert($this->objectName, $this->properties);
        } else {
            $values = [];
            foreach ($this->properties as $key => &$property) {
                if ($property[StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] == false
                    || $this->queryHelper->isPrimaryKey($property)
                ) {
                    continue;
                }
                $values[$key] = $property[StoredObject::PROPERTY_ATTRIBUTE_VALUE];
            }
            $query = $this->queryHelper->buildUpdate($this->objectName, $values, "id = $this->id");
        }

        $st = $this->dbHelper->execute($query, $this->queryHelper->getQueryValues());
        $this->queryHelper->clearQueryValues();

        if ($this->isNew) {
            $this->id = $this->dbHelper->lastInsertId();
        }

        parent::save();
    }

    /**
     * Удаление записи из таблицы
     * @return string
     * @throws Exception выбрасывается если невозможно удалить запись из таблицы
     */
    public function delete(): string
    {
        $query = $this->queryHelper->buildDelete($this->objectName, "id=$this->id");
        $st = $this->dbHelper->execute($query);
        return $st->errorCode();
    }

    /**
     * Сериализация объекта
     * @return string
     */
    public function __toString(): string
    {
        return $this->objectName . ' ' . json_encode($this->properties);
    }

    /**
     * Инициализация процедуры выборки объектов из БД
     * Начало генерации запроса SELECT
     * @param string $from переопределить выборку из таблицы
     * например, если для таблицы нужно указать алиас
     * @param string[] $values массив с именами полей
     * например ['field_1', field_2]
     * или ['tableName.field_1', 'tableName.field_2']
     * или ['field_1 f1', 'field_2 f2']
     * @param array|null $dbConfig
     * @return RDBRecord объект, в рамках которого вызывался метод select
     * со сгенерированным началом запроса
     */
    public static function select(string $from, $values = ["*"], ?array $dbConfig = null): RDBRecord
    {
        $classname = get_called_class();
        /**
         * @var $obj RDBRecord
         */
        $obj = new $classname(null, $from, $dbConfig);

        $obj->sqlQuery = $obj->queryHelper->buildSelect($from, $values);
        return $obj;
    }

    /**
     * Указание критерия для запроса (используется при вызове select или update)
     * @param string $where критерий запроса, который описывает блок WHERE
     * @param array|null $values
     * @return RDBRecord объект, в рамках которого был дополнен запрос
     */
    public function where(string $where, ?array $values = null): RDBRecord
    {
        $this->sqlQuery .= $this->queryHelper->addWhere($where, $values);
        return $this;
    }

    /**
     * Инициализация процедуры обновления данных в БД
     * @param array $values массив key=>value [string=>mixed]
     * @param string|null $table Имя таблицы, для которой необходимо выполнить запрос
     * @param array|null $dbConfig
     * @return RDBRecord
     */
    public static function update(array $values, ?string $table = null, ?array $dbConfig = null): RDBRecord
    {
        $classname = get_called_class();
        $obj = new $classname(null, $table, $dbConfig);

        if (!RDBSchemaRecord::isSchemaExists($obj->objectName)) {
            $obj->parseSchema($obj->objectName);
        }

        $obj->sqlQuery = $obj->queryHelper->buildUpdate(
            $obj->objectName, $values
        );
        return $obj;
    }

    /**
     * Объединение с другой таблицей при вызове select
     * @param string $type тип объединения (объявлены внутри mmvc\models\DBTable)
     * @param string $tableName имя таблицы, с которой происходит объединение
     * @param string $on критерий объединения
     * используется только для объединений с типами DBTable::JOIN_TYPE_LEFT и
     * DBTable::JOIN_TYPE_RIGHT
     * @return RDBRecord
     */
    public function join(string $type, string $tableName, string $on = ''): RDBRecord
    {
        $this->sqlIsJoin = true;
        $this->sqlQuery = $this->queryHelper->addJoin($this->sqlQuery, $type, $tableName, $on);
        return $this;
    }

    /**
     * Установка лимита на выборку
     * @param integer $limit сколько
     * @param integer $offset смещение
     * @return RDBRecord
     */
    public function limit(int $limit, $offset = 0): RDBRecord
    {
        $this->sqlQuery = $this->queryHelper->addLimit($this->sqlQuery, $limit, $offset);
        return $this;
    }

    /**
     * Выполнение запроса, сгенерированного ранее для объекта
     * через вызовы методов select, update, where, join
     * @return mixed если данные есть то ArrayObject
     * или \mmvc\models\data\RDBTable в зависимости от количества найденных объектов или null
     */
    public function execute()
    {
        /**
         * Получаем схему, если ее нет в скрипте
         */
        if ($this->sqlIsJoin !== false || RDBSchemaRecord::getSchema($this->objectName) === null) {
            $this->parseSchema($this->objectName);
        }

        $st = $this->dbHelper->execute($this->sqlQuery, $this->queryHelper->getQueryValues());

        $this->queryHelper->clearQueryValues();

        return $this->postExecute($st, $this->getClassName(), $this->objectName);
    }

    /**
     *
     * @param PDOStatement|null $st
     * @param $classname
     * @param $tableName
     * @return \ArrayObject|string
     */
    protected function postExecute(?PDOStatement $st, string $classname, string $tableName)
    {
        $queryType = substr($this->sqlQuery, 0, strpos($this->sqlQuery, ' '));
        $result = null;

        switch ($queryType) {
            case 'SELECT':
                $ignoreSchema = $this->sqlIsJoin;
                $result = new \ArrayObject();
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $obj = new $classname();
                    $obj->isNew = false;
                    $obj->fillProperties($row, $ignoreSchema);
                    $result->append($obj);
                }
                break;
            case 'INSERT':
                $result = $this->dbHelper->lastInsertId();
                break;
            case 'UPDATE':
            default:
                $result = $st->errorCode();
                break;
        }

        $this->sqlQuery = "";
        $this->sqlIsJoin = false;
        $this->isNew = false;

        return $result;
    }


    // mssql sp_help "[SchemaName].[TableName]" 
    // firebird show table "table_name"
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string|null $table
     */
    public function parseSchema(?string $table = null)
    {
        if ($table === null) {
            $table = $this->objectName;
        }

        if (RDBSchemaRecord::isSchemaExists($table)) {
            return;
        }

        $query = $this->queryHelper->buildDescribe($table);
        $st = $this->dbHelper->execute($query);

        $schema = new RDBSchemaRecord();
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $schema->addColumn($table, $row);
        }
    }


}
