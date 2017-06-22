<?php

namespace app\models\data;

use \PDO;
use app\models\BaseModel;
use app\models\data\QueryHelper;
use app\models\data\sql\AbstractQueryHelper;

/**
 * Представление выборки данных из таблицы или таблиц как объекта со свойствами
 */
class RDBRecord extends StoredObject {
    
    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT  = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL  = 'FULL';
    
    const PROPERTY_ATTRIBUTE_SCHEMA   = 'schema';
    const PROPERTY_ATTRIBUTE_TYPE   = 'type';
    
    /**
     * Инстанс объекта для работы с БД
     * @var \app\models\data\RDBHelper 
     */
    protected $dbHelper;
    
    /**
     * Хелпер для генерации запросов в СУБД
     * @var app\models\data\sql\AbstractQueryHelper 
     */
    protected $queryHelper;

    /*
     * текущий сгенерированный запрос через методы select, where, update, join     
     * затирается после выполнения запроса
     * @var string
     */
    private $sql_query;
    
    /* присутствует ли join с таблицей внутри запроса $sql_query
     * устанавливается в true при вызове join
     * затирается после выполнения запроса
     * @var boolean
     */
    private $sql_is_join;
    
    /**
     * Схема данных для таблицы (одна для всех существующих объектов каждой таблицы)
     * schema[tablename] = ['type' => string, 'size' => integer, 'default' => mixed]
     * Заполняется при первом обращении к таблице запросом DESCRIBE $tablename
     * @var array 
     */
    protected static $schema;

    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param integer $id PK записи для выгрузки существующий (опционально)
     */
    public function __construct($id = null, $dbConfig = null) {
        
        parent::__construct();
        $this->dbHelper = new RDBHelper($dbConfig);
        $this->queryHelper = $this->dbHelper->getQueryHelper();
        
        $this->is_new = ($id === null);
        // Если не новый объект (уже находится в БД)
        if (!$this->is_new) {
            $this->initStored($id);
        }    
        $this->sql_query = "";
        $this->sql_is_join = false;
        $this->first_load = false;
    }
    
    /**
     * 
     * @return app\models\data\sql\AbstractQueryHelper
     */
    protected function getQueryHelper()
    {
        $obj = $this->dbHelper->getQueryHelper();        
        return $obj;
    }

    /**
     * Загрузка существующего в БД объекта по первичному ключу
     * @param integer $id
     */
    protected function initStored($id) {
        if (!RDBHelper::isSchemaExists($this->object_name)) {
            $this->dbHelper->parseSchema($this->object_name);
        }
        
        $sql = $this->queryHelper->buildSelect('*', $this->object_name, "id=$id");
        $st = $this->dbHelper->execute($sql);

        $this->fillProperties($st->fetch(PDO::FETCH_ASSOC));
    }

    protected function fillProperties($props, $ignore_schema = false) {
        foreach ($props as $key => $value) {
            if (self::isPropertyExists($this->object_name, $key) || $ignore_schema === true) {                
                $this->__set($key, htmlspecialchars($value));
                $this->properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = false;
            }
        }
    }

    /**
     * Получение схемы таблицы для записи
     * @return array
     */
    public function getObjectSchema() {
        return self::getSchema($this->objectName);
    }   

    protected function isPrimaryKey($name) {
        $data = $this->properties[$name];
        return isset($data['flags']) && ($data['flags'] & MYSQLI_PRI_KEY_FLAG);
    }

    /**
     * Возвращает название колонки Primary key
     * @return string
     */
    protected function getPrimaryColumn() {
        
        return QueryHelper::getPrimaryColumn($this->properties);
    }    

    /**
     * Сохранение модели как записи в БД
     * Для созданной модели генерируется запрос INSERT INTO
     * А также свойство is_new устанавливается в false
     * Для найденной UPDATE
     * Свойство is_dirty для каждого атрибута ставится в false на этапе генерации запроса
     * @throws \Exception
     */
    public function save() {
        $query = '';
        if ($this->is_new) {
            $this->dbHelper->parseSchema($this->object_name);
            $query = $this->queryHelper->buildInsertQuery($this->properties);
        } else {
            $query = $this->queryHelper->buildUpdateQuery($this->properties);
        }

        $st = $this->dbHelper->execute($query);

        if ($this->is_new) {
            $this->id = $this->dbHelper->lastInsertId();
        }

        parent::save();
    }

    /**
     * Удаление записи из таблицы
     * @return boolean
     * @throws Exception выбрасывается если невозможно удалить запись из таблицы
     */
    public function delete() {
        $query = $this->queryHelper->buildDelete($this->objectName, "id=$this->id");
        $st = $this->dbHelper->execute($query);
        $errCode = $st->errorCode();
        return $errCode;
    }

    /**
     * Сериализация объекта
     * @return string
     */
    public function __toString() {
        return $this->objectName . ' ' . json_encode($this->properties);
    }

    /**
     * Инициализация процедуры выборки объектов из БД
     * Начало генерации запроса SELECT
     * @param array $values массив с именами полей
     * например ['field_1', field_2]
     * или ['tableName.field_1', 'tableName.field_2']
     * или ['field_1 f1', 'field_2 f2']
     * @param string $from переопределить выборку из таблицы
     * например, если для таблицы нужно указать алиас
     * @return \app\models\data\RDBRecord объект, в рамках которого вызывался метод select
     * со сгенерированным началом запроса
     */
    public static function select($values = "*", $from = null) {
        $classname = get_called_class();
        $obj = new $classname();
        if ($from === null)
        {
            $from = $obj->objectName;
        }
        $obj->sql_query = $obj->queryHelper->buildSelect(
                $values,
                $from);
        return $obj;
    }

    /**
     * Указание критерия для запроса (используется при вызове select или update)
     * @param string $where критерий запроса, который описывает блок WHERE
     * @return \app\models\RDBTable объект, в рамках которого был дополнен запрос
     */
    public function where($where) {
        $this->sql_query .= $this->queryHelper->addWhere($where);       
        return $this;
    }

    /**
     * Инициализация процедуры обновления данных в БД
     * @param array $values массив key=>value [string=>mixed]
     * @return \app\models\data\RDBRecord
     */
    public static function update($values) {
        $classname = get_called_class();
        $obj = new $classname();
        if (!self::isSchemaExists($obj->objectName))
        {
            self::parseSchema($obj->objectName);
        }
        
        $obj->sql_query = $this->queryHelper->buildUpdate(
                $obj->objectName,
                $values
                );
        return $obj;
    }

    /**
     * Объединение с другой таблицей при вызове select
     * @param string $type тип объединения (объявлены внутри app\models\DBTable)
     * @param string $table_name имя таблицы, с которой происходит объединение
     * @param string $on критерий объединения
     * используется только для объединений с типами DBTable::JOIN_TYPE_LEFT и
     * DBTable::JOIN_TYPE_RIGHT
     * @return \app\models\data\RDBRecord
     */
    public function join($type, $table_name, $on = null) {
        $this->sql_is_join = true;
        $this->sql_query = $this->queryHelper->addJoin(
                $this->sql_query, 
                $type, 
                $table_name, 
                $on
                );
        return $this;
    }

    /**
     * Установка лимита на выборку
     * @param integer $limit сколько
     * @param integer $offset смещение
     * @return \app\models\data\RDBRecord
     */
    public function limit($limit, $offset = 0) {
        $this->sql_query = $this->queryHelper->addLimit(
                $this->sql_query,
                $limit, 
                $offset
                );
        return $this;
    }

    /**
     * Выполнение запроса, сгенерированного ранее для объекта
     * через вызовы методов select, update, where, join
     * @return mixed если данные есть то ArrayObject 
     * или \app\models\data\RDBTable в зависимости от количества найденных объектов или null 
     */
    public function execute() {
        if ($this->sql_is_join !== false && RDBRecord::getSchema($this->objectName) === null) {
            RDBRecord::parseSchema($this->objectName);
        }
        $this->sql_query .= ";";
        /**
         * Получаем схему, если ее нет в скрипте
         */
        if (RDBRecord::getSchema($this->objectName) === null) {
            RDBRecord::parseSchema($this->objectName);
        }
        $st = $this->dbHelper->execute($this->sql_query);

        return $this->postExecute($st, $this->getClassName(), $this->objectName);
    }

    /**
     * 
     * @param \PDOStatement $st
     */
    protected function postExecute($st, $classname, $table_name) {
        $queryType = substr($this->sql_query, 0, strpos($this->sql_query, ' '));
        $result = null;

        switch ($queryType) {
            case 'SELECT':
                $ignore_schema = $this->sql_is_join;
                $result = new \ArrayObject();
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $obj = new $classname();
                    $obj->is_new = false;
                    $obj->fillProperties($row, $ignore_schema);
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

        $this->sql_query = "";
        $this->sql_is_join = false;
        $this->is_new = false;

        return $result;
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
    
    // mssql sp_help "[SchemaName].[TableName]" 
    // firebird show table "table_name"
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public function parseSchema($table_name) {
        
        if (RDBRecord::isSchemaExists($table_name)) {
            return;
        }
        
        $query = $this->queryHelper->buildDescribe($table_name);
        $st = $this->dbHelper->execute($query);
        
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            self::$schema[$table_name][$row['Field']] = [
                'type' => self::getType($row['Type']),
                'size' => self::getTypeSize($row['Type']),
                'default' => $row['Default'],
            ];
        }
    }
}
