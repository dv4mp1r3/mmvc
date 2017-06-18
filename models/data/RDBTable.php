<?php

namespace app\models\data;

use \PDO;

/**
 * TODO
 * вынести код генерации запросов в отдельный класс
 * в нем учитывать возможные варианты для одинаковых по типу запросов
 * (для разных СУБД)
 * добавить экранирование
 */
class RDBTable {

    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_OUTER = 'OUTER';
    const JOIN_TYPE_FULL = 'FULL';

    /**
     * Инстанс объекта для работы с БД
     * @var \app\models\data\RDBHelper 
     */
    protected $dbHelper;
    // prop = ['name' => ['is_dirty' => false, 'schema' => 'integer', 'value' => 1]]
    protected $properties;
    protected $is_new;
    protected $object_name;
    protected $first_load = true;
    // текущий сгенерированный запрос через методы select, where, update, join
    // затирается после выполнения запроса
    private $sql_query;
    // присутствует ли join с таблицей внутри запроса $sql_query
    // устанавливается в true при вызове join
    // затирается после выполнения запроса
    private $sql_is_join;

    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param integer $id PK записи для выгрузки существующий (опционально)
     */
    public function __construct($id = null) {
        $classname = get_called_class();
        $this->object_name = substr($classname, strrpos($classname, '\\') + 1);

        $this->dbHelper = new RDBHelper();

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
     * Загрузка существующего в БД объекта по первичному ключу
     * @param integer $id
     */
    protected function initStored($id) {
        if (!RDBHelper::isSchemaExists($this->object_name)) {
            $this->dbHelper->parseSchema($this->object_name);
        }
        $sql = "SELECT * FROM $this->object_name WHERE id=$id";
        $st = $this->dbHelper->execute($sql);

        $this->fillProperties($st->fetch(PDO::FETCH_ASSOC));
    }

    protected function fillProperties($props, $ignore_schema = false) {
        foreach ($props as $key => $value) {
            if (RDBHelper::isPropertyExists($this->object_name, $key) || $ignore_schema === true) {
                $this->__set($key, $value);
                $this->properties[$key]['is_dirty'] = false;
            }
        }
    }

    /**
     * Получение схемы таблицы для записи
     * @return array
     */
    public function getSchema() {
        return $this->dbHelper->getSchema($this->object_name);
    }

    /**
     * Проверка, было ли свойство модели модифицировано после извлечения из БД
     * @param string $name
     * @return boolean true если свойство было модифицировано, но не сохранено в БД
     */
    protected function isDirtyProperty($name) {
        $data = $this->properties[$name];
        return isset($data['is_dirty']) && $data['is_dirty'] === true;
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
        foreach ($this->properties as $key => $data) {
            if ($data['flags'] & MYSQLI_PRI_KEY_FLAG) {
                return $key;
            }
        }
    }

    /**
     * Создание запроса для добавления записи в базу
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос INSERT INTO $tablename ($columns) VALUES ($values);
     */
    private function buildInsertQuery() {
        $props = '';
        $values = '';
        $delemiter = ', ';
        foreach ($this->properties as $key => $data) {
            if (!isset($data['value']) || $this->isPrimaryKey($key)) {
                continue;
            }

            if (strlen($props) > 0) {
                $props .= $delemiter;
            }
            $props .= "`$key`";

            if (strlen($values) > 0) {
                $values .= $delemiter;
            }
            $this->properties[$key]['is_dirty'] = false;
            $value = $this->serializeProperty($data["value"], RDBHelper::getTypeName($this->object_name, $key));
            $values .= "'" . str_replace("'", "", $value) . "'";
        }
        $q = "INSERT INTO $this->object_name ($props) VALUES ($values);";
        return $q;
    }

    /**
     * Создание запроса для обновления данных для существующей записи
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос UPDATE $table_name SET (values) WHERE id=$id;
     * @throws Exception выбрасывается, если у объекта нет измененных свойств
     */
    private function buildUpdateQuery() {
        $values = '';
        $new_values = 0;
        foreach ($this->properties as $key => $data) {
            if (!$this->isDirtyProperty($key) || $this->isPrimaryKey($key)) {
                continue;
            }

            if (strlen($values) > 0) {
                $values .= ', ';
            }
            $value = $this->serializeProperty($data["value"], $data["type"]);
            $values .= "`$key`=$value";

            $this->properties[$key]['is_dirty'] = false;
            $new_values++;
        }
        if ($new_values === 0) {
            throw new \Exception('Model has no changed properties');
        }
        $q = "UPDATE $this->object_name SET $values WHERE `id`='$this->id'";
        return $q;
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
            $query = $this->buildInsertQuery();
        } else {
            $query = $this->buildUpdateQuerty();
        }


        $st = $this->dbHelper->execute($query);

        if ($this->is_new) {
            $this->id = $this->dbHelper->lastInsertId();
        }

        $this->is_new = false;
    }

    /**
     * Удаление записи из таблицы
     * @return boolean
     * @throws Exception выбрасывается если невозможно удалить запись из таблицы
     */
    public function delete() {
        $query = "DELETE from $this->object_name WHERE id=$this->id";
        $st = $this->dbHelper->execute($query);
        $errCode = $st->errorCode();
        return $errCode;
    }

    /**
     * Сериализация объекта
     * @return string
     */
    public function __toString() {
        return $this->object_name . ' ' . json_encode($this->properties);
    }

    /**
     * Приведение свойства объекта к строке для записи в БД
     * @param mixed $value значение объекта
     * @param string $type название типа данных в строковом представлении
     * @return string строковое представление типа данных
     * @throws Exception генерируется если передаваемый тип неизвестен
     * Или если передан тип set, но $variable не массив
     */
    private function serializeProperty($value, $type) {
        $type = strtolower($type);
        switch ($type) {
            case 'integer':
            case 'int':
                return (string) intval($value);
            case 'string':
            case 'enum':
            case 'tinytext':
            case 'mediumtext':
            case 'varchar':
                return "'" . $this->filterString($value) . "'";
            case 'double':
                return (string) floatval($value);
            case 'set':
                if (is_array($value))
                    return "(" . implode(", ", $value) . ")";
                throw new Exception("Variable 'value' is not array.");
            case 'bit':
                return boolval($value) ? "1" : "0";
            default:
                throw new \Exception("Unknown type $type.");
        }
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
     * @return \app\models\data\RDBTable объект, в рамках которого вызывался метод select
     * со сгенерированным началом запроса
     */
    public static function select($values = "*", $from = null) {
        $classname = get_called_class();
        $obj = new $classname();
        if (!is_array($values)) {
            $obj->sql_query = "SELECT * ";
        } else {
            foreach ($values as &$value) {
                $value = self::filterString($value);
            }
            $fields = implode(", ", $values);
            $obj->sql_query = "SELECT $fields ";
        }
        $obj->sql_query .= "FROM " . ($from === null ? $obj->object_name : $from);
        return $obj;
    }

    /**
     * Указание критерия для запроса (используется при вызове select или update)
     * @param string $where критерий запроса, который описывает блок WHERE
     * @return \app\models\RDBTable объект, в рамках которого был дополнен запрос
     */
    public function where($where) {
        $this->sql_query .= " WHERE $where ";
        return $this;
    }

    /**
     * Инициализация процедуры обновления данных в БД
     * @param array $values массив key=>value [string=>mixed]
     * @return \app\models\data\RDBTable
     */
    public static function update($values) {
        $classname = get_called_class();
        $obj = new $classname();

        RDBHelper::parseSchema($obj->object_name);
        $set = '';
        foreach ($values as $key => $value) {
            if (strlen($set) > 0) {
                $set .= ', ';
            }
            $value = $obj->serializeProperty($value, RDBHelper::getTypeName($obj->object_name, $key));
            $set .= "`$key`=$value";
        }
        $obj->sql_query = "UPDATE $obj->table_name SET $set ";
        return $obj;
    }

    /**
     * Объединение с другой таблицей при вызове select
     * @param string $type тип объединения (объявлены внутри app\models\DBTable)
     * @param string $table_name имя таблицы, с которой происходит объединение
     * @param string $on критерий объединения
     * используется только для объединений с типами DBTable::JOIN_TYPE_LEFT и
     * DBTable::JOIN_TYPE_RIGHT
     * @return \app\models\data\RDBTable
     */
    public function join($type, $table_name, $on = null) {
        $this->sql_query .= " $type JOIN $table_name ON $on";
        $this->sql_is_join = true;
        return $this;
    }

    /**
     * Установка лимита на выборку
     * @param integer $limit сколько
     * @param integer $offset смещение
     * @return \app\models\data\RDBTable
     */
    public function limit($limit, $offset = 0) {
        $limit = intval($limit);
        $offset = intval($offset);
        $this->sql_query .= " LIMIT $offset, $limit ";
        return $this;
    }

    /**
     * Выполнение запроса, сгенерированного ранее для объекта
     * через вызовы методов select, update, where, join
     * @return mixed если данные есть то ArrayObject 
     * или \app\models\data\RDBTable в зависимости от количества найденных объектов или null 
     */
    public function execute() {
        $classname = get_called_class();
        $table_name = substr($classname, strrpos($classname, '\\') + 1);

        if ($this->sql_is_join !== false && RDBHelper::getSchema($table_name) === null) {
            $this->dbHelper->parseSchema($table_name);
        }

        $this->sql_query .= ";";

        /**
         * Получаем схему, если ее нет в скрипте
         */
        if (RDBHelper::getSchema($table_name) === null) {
            $this->dbHelper->parseSchema($table_name);
        }
        $st = $this->dbHelper->execute($this->sql_query);

        return $this->postExecute($st, $classname, $table_name);
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

        return $result;
    }

    /**
     * Фильтрация строки, используемая для работы с БД и/или
     * вывода на страницу
     * @param string $value
     * @return string
     */
    private static function filterString($value) {
        return $value;
        //return mysql_real_escape_string($value);
    }

    /**
     * Представление объекта в виде массива $object['attribute'] = $value
     * @return array
     */
    public function asArray() {
        $data = array();
        foreach ($this->properties as $key => $property) {
            $data[$key] = $property['value'];
        }

        return count($data) > 0 ? $data : null;
    }

    /**
     * Представление массива в виде json строки
     * @return string
     */
    public function asJson() {
        return json_encode(self::asArray());
    }

    public function __get($name) {
        return $this->properties[$name]['value'];
    }

    public function __set($name, $value) {
        $this->properties[$name]['value'] = $value;
        if (!$this->first_load && $name !== 'id') {
            $this->properties[$name]['is_dirty'] = true;
        }
    }

}
