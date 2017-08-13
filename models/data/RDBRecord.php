<?php namespace app\models\data;

use \PDO;
use app\models\BaseModel;
use app\models\data\QueryHelper;
use app\models\data\sql\AbstractQueryHelper;
use app\models\data\RDBHelper;

/**
 * Представление выборки данных из таблицы или таблиц как объекта со свойствами
 */
class RDBRecord extends StoredObject
{

    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL = 'FULL';
    const PROPERTY_ATTRIBUTE_SCHEMA = 'schema';
    const PROPERTY_ATTRIBUTE_TYPE = 'type';

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
    private $sqlQuery;

    /* присутствует ли join с таблицей внутри запроса $sql_query
     * устанавливается в true при вызове join
     * затирается после выполнения запроса
     * @var boolean
     */
    private $sqlIsJoin;

    /**
     * Схема данных для таблицы (одна для всех существующих объектов каждой таблицы)
     * schema[tablename] = ['type' => string, 'size' => integer, 'default' => mixed]
     * Заполняется при первом обращении к таблице запросом DESCRIBE $tablename
     * @var array 
     * @see RDBRecord::parseSchema
     */
    protected static $schema;

    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param integer $id PK записи для выгрузки существующий (опционально)
     */
    public function __construct($id = null, $table = null, $dbConfig = null)
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
    protected function initStored($id)
    {
        if (!RDBHelper::isSchemaExists($this->objectName)) {
            $this->parseSchema($this->objectName);
        }

        $sql = $this->queryHelper->buildSelect('*', $this->objectName, "id=$id");
        $st = $this->dbHelper->execute($sql);

        $this->fillProperties($st->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Заполнение свойств объекта из результатов запроса
     * @param array $props результат выполнения запроса
     * @see app\models\data\RDBRecord::execute()
     * @param boolean $ignore_schema нужно ли игнорировать схему при заполнении свойств
     * Например, при выполнении join и помещении результатов в один объект
     */
    protected function fillProperties($props, $ignore_schema = false)
    {
        foreach ($props as $key => $value) {
            if (self::isPropertyExists($this->objectName, $key) || $ignore_schema === true) {
                $this->__set($key, $value);
                $this->properties[$key][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = false;
            }
        }
    }

    /**
     * Перегрузка метода присваивания значения свойству
     * При изменении значения id ему не выставляется свойство is_dirty в true
     * @see app\models\data\StoredObject::__set()
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
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
    public function getObjectSchema()
    {
        return self::getSchema($this->objectName);
    }

    protected function isPrimaryKey($name)
    {
        $data = $this->properties[$name];
        return isset($data['flags']) && ($data['flags'] & MYSQLI_PRI_KEY_FLAG);
    }

    /**
     * Возвращает название колонки Primary key
     * @return string
     */
    protected function getPrimaryColumn()
    {
        return $this->queryHelper->getPrimaryColumn($this->properties);
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
            $query = $this->queryHelper->buildInsertQuery($this->objectName, $this->properties);
        } else {
            $query = $this->queryHelper->buildUpdateQuery($this->objectName, $this->properties);
        }
        
        $st = $this->dbHelper->execute($query, $this->queryHelper->getQueryValues());

        if ($this->isNew) {
            $this->id = $this->dbHelper->lastInsertId();
        }

        parent::save();
    }

    /**
     * Удаление записи из таблицы
     * @return boolean
     * @throws Exception выбрасывается если невозможно удалить запись из таблицы
     */
    public function delete()
    {
        $query = $this->queryHelper->buildDelete($this->objectName, "id=$this->id");
        $st = $this->dbHelper->execute($query);
        $errCode = $st->errorCode();
        return $errCode;
    }

    /**
     * Сериализация объекта
     * @return string
     */
    public function __toString()
    {
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
    public static function select($values = "*", $from = null, $dbConfig = null)
    {
        $classname = get_called_class();
        $obj = new $classname(null, $from, $dbConfig);

        $obj->sqlQuery = $obj->queryHelper->buildSelect(
            $values, $obj->objectName
        );
        return $obj;
    }

    /**
     * Указание критерия для запроса (используется при вызове select или update)
     * @param string $where критерий запроса, который описывает блок WHERE
     * @return \app\models\RDBTable объект, в рамках которого был дополнен запрос
     */
    public function where($where, $values = null)
    {
        $this->sqlQuery .= $this->queryHelper->addWhere($where, $values);
        return $this;
    }

    /**
     * Инициализация процедуры обновления данных в БД
     * @param array $values массив key=>value [string=>mixed]
     * @param string $table Имя таблицы, для которой необходимо выполнить запрос
     * @return \app\models\data\RDBRecord
     */
    public static function update($values, $table = null, $dbConfig = null)
    {
        $classname = get_called_class();
        $obj = new $classname(null, $table, $dbConfig);

        if (!self::isSchemaExists($obj->objectName)) {
            $obj->parseSchema($obj->objectName);
        }

        $obj->sqlQuery = $obj->queryHelper->buildUpdate(
            $obj->objectName, $values
        );
        return $obj;
    }

    /**
     * Объединение с другой таблицей при вызове select
     * @param string $type тип объединения (объявлены внутри app\models\DBTable)
     * @param string $tableName имя таблицы, с которой происходит объединение
     * @param string $on критерий объединения
     * используется только для объединений с типами DBTable::JOIN_TYPE_LEFT и
     * DBTable::JOIN_TYPE_RIGHT
     * @return \app\models\data\RDBRecord
     */
    public function join($type, $tableName, $on = null)
    {
        $this->sqlIsJoin = true;
        $this->sqlQuery = $this->queryHelper->addJoin(
            $this->sqlQuery, $type, $tableName, $on
        );
        return $this;
    }

    /**
     * Установка лимита на выборку
     * @param integer $limit сколько
     * @param integer $offset смещение
     * @return \app\models\data\RDBRecord
     */
    public function limit($limit, $offset = 0)
    {
        $this->sqlQuery = $this->queryHelper->addLimit(
            $this->sqlQuery, $limit, $offset
        );
        return $this;
    }

    /**
     * Выполнение запроса, сгенерированного ранее для объекта
     * через вызовы методов select, update, where, join
     * @return mixed если данные есть то ArrayObject 
     * или \app\models\data\RDBTable в зависимости от количества найденных объектов или null 
     */
    public function execute()
    {
        if ($this->sqlIsJoin !== false && RDBRecord::getSchema($this->objectName) === null) {
            $this->parseSchema($this->objectName);
        }
        //$this->sqlQuery .= ";";
        /**
         * Получаем схему, если ее нет в скрипте
         */
        if (RDBRecord::getSchema($this->objectName) === null) {
            $this->parseSchema($this->objectName);
        }

        $st = $this->dbHelper->execute($this->sqlQuery, $this->queryHelper->getQueryValues());

        $this->queryHelper->clearQueryValues();

        return $this->postExecute($st, $this->getClassName(), $this->objectName);
    }

    /**
     * 
     * @param \PDOStatement $st
     */
    protected function postExecute($st, $classname, $tableName)
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

    public static function isPropertyExists($tableName, $propertyName)
    {
        return isset(self::$schema[$tableName][$propertyName]);
    }

    public static function getSchema($tableName)
    {
        if (self::isSchemaExists($tableName)) {
            return self::$schema[$tableName];
        }
        
        return null;
    }

    public static function isSchemaExists($tableName)
    {
        return empty(self::$schema) ? false : !empty(self::$schema[$tableName]);
    }

    /**
     * Получение имени типа из загруженной ранее схемы
     * @param string $tableName
     * @param string $field
     */
    public static function getTypeName($tableName, $field)
    {
        if (!isset(self::$schema[$tableName])) {
            throw new \Exception("Schema for table $tableName is not loaded yet");
        }

        return self::$schema[$tableName][$field]['type'];
    }

    /**
     * Получение типа данных из строки вида type(size), полученной из запроса DESCRIBE
     * @param string $type
     * @return string
     */
    protected function getType($type)
    {
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
    protected function getTypeSize($type)
    {
        $begin = strpos($type, '(');
        return (int) substr($type, $begin + 1, strlen($type) - $begin - 2);
    }

    // mssql sp_help "[SchemaName].[TableName]" 
    // firebird show table "table_name"
    /**
     * Обработка схемы таблицы и занесение в массив self::$schema
     * @param string $table_name
     */
    public function parseSchema($table = null)
    {
        if ($table === null)
            $table = $this->objectName;

        if (RDBRecord::isSchemaExists($table)) {
            return;
        }

        $query = $this->queryHelper->buildDescribe($table);
        $st = $this->dbHelper->execute($query);

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            self::$schema[$table][$row['Field']] = [
                'type' => self::getType($row['Type']),
                'size' => self::getTypeSize($row['Type']),
                'default' => $row['Default'],
            ];
        }
    }
    
    /**
     * Получение типа данных PHP для свойства по его имени
     * с учетом схемы данных СУБД
     * @param string $propertyName
     * @return string
     * @throws \Exception выбрасывается если нет схемы 
     */
    public function getPropertyType($propertyName)
    {
        $table = $this->objectName;
        if (!self::isSchemaExists($table))
        {
            throw new \Exception('Empty schema for table '.$table);
        }
        $propType = self::$schema[$table][$propertyName][RDBRecord::PROPERTY_ATTRIBUTE_TYPE];
        
        return $this->queryHelper->getPropertyType($propType);
    }
}
