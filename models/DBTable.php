<?php

namespace app\models;

use app\models\DBHelper;

class DBTable extends BaseModel
{
    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_INNER = 'INNER';
    
    // prop = ['name' => ['is_dirty' => false, 'schema' => 'integer', 'value' => 1]]
    private $properties;
    private $is_new;
    private $table_name;
    private $first_load = true;

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
    public function __construct($id = null)
    {
        $classname        = get_called_class();
        $this->table_name = substr($classname, strrpos($classname, '\\') + 1);

        $this->is_new = ($id === null);
        if (!$this->is_new) {
            if (DBHelper::isConnected()) {
                DBHelper::createConnection();
            }
            $db_result = DBHelper::$connection->
                query("SELECT * FROM $this->table_name WHERE id=$id");
            
            DBHelper::parseSchema($this->table_name);
            $this->fillProperties(mysqli_fetch_array($db_result));     
        }
        $this->sql_query = "";
        $this->sql_is_join = false;
        $this->first_load = false;
    }

    /**
     * Получение схемы таблицы для записи
     * @return array
     */
    public function getSchema()
    {
        return DBHelper::getSchema($this->table_name);
    }

    /**
     * Проверка, было ли свойство модели модифицировано после извлечения из БД
     * @param string $name
     * @return boolean true если свойство было модифицировано, но не сохранено в БД
     */
    protected function isDirtyProperty($name)
    {
        $data = $this->properties[$name];
        return isset($data['is_dirty']) && $data['is_dirty'] === true;
    }

    protected function isPrimaryKey($name)
    {
        $data = $this->properties[$name];
        return isset($data['flags']) && ($data['flags'] & MYSQLI_PRI_KEY_FLAG);
    }

    /**
     * Создание запроса для добавления записи в базу
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос INSERT INTO $tablename ($columns) VALUES ($values);
     */
    private function buildInsertQuery()
    {
        $props     = '';
        $values    = '';
        $delemiter = ', ';
        foreach ($this->properties as $key => $data) {
            if (!isset($data['value']) || $this->isPrimaryKey($key)) continue;

            if (strlen($props) > 0) {
                $props .= $delemiter;
            }
            $props .= "`$key`";

            if (strlen($values) > 0) {
                $values .= $delemiter;
            }
            $this->properties[$key]['is_dirty'] = false;
            $value  = $this->serializeProperty($data["value"], DBHelper::getTypeName($this->table_name, $key));            
            $values .= "'".  str_replace("'", "", $value)."'";
        }
        $q = "INSERT INTO $this->table_name ($props) VALUES ($values);";
        return $q;
    }

    /**
     * Создание запроса для обновления данных для существующей записи
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос UPDATE $table_name SET (values) WHERE id=$id;
     * @throws Exception выбрасывается, если у объекта нет измененных свойств
     */
    private function buildUpdateQuerty()
    {
        $values     = '';
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
        $q = "UPDATE $this->table_name SET $values WHERE `id`='$this->id'";
        return $q;
    }

    /**
     * Возвращает название колонки Primary key
     * @return string
     */
    protected function getPrimaryColumn()
    {
        foreach ($this->properties as $key => $data) {
            if ($data['flags'] & MYSQLI_PRI_KEY_FLAG) {
                return $key;
            }
        }
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
        if ($this->is_new) {
            DBHelper::parseSchema($this->table_name);
            $query = $this->buildInsertQuery();
        } else {
            $query = $this->buildUpdateQuerty();
        }
        
        if (DBHelper::$connection === null) {
            DBHelper::createConnection();
        }
        $result = DBHelper::$connection->query($query);

        if (!$result) {
            throw new \Exception('MySQL error: '.DBHelper::$connection->error);
        }

        if ($this->is_new) {
            $this->id = DBHelper::$connection->insert_id;
        }

        $this->is_new = false;
    }

    public function __get($name)
    {
        return $this->properties[$name]['value'];
    }

    public function __set($name, $value)
    {
        $this->properties[$name]['value']    = $value;
        if (!$this->first_load && $name !== 'id') {
            $this->properties[$name]['is_dirty'] = true;
        }
    }

    
    protected function fillProperties($props, $ignore_schema = false)
    {
        foreach ($props as $key => $value) {
            if (DBHelper::isPropertyExists($this->table_name, $key) 
                    || $ignore_schema === true) {
                $this->__set($key, $value);
                $this->properties[$key]['is_dirty'] = false;
            }
        }
    }

    /**
     * Удаление записи из таблицы
     * @return boolean
     * @throws Exception выбрасывается если невозможно удалить запись из таблицы
     */
    public function delete()
    {
        $query = "DELETE from $this->table_name WHERE id=$this->id";
        if (DBHelper::$connection->query($query)) {
            return true;
        }

        throw new \Exception("Can't delete row from $this->table_name "
        ."with id $this->id. Error: ".
        DBHelper::$connection->error);
    }

    /**
     * Сериализация объекта
     * @return string
     */
    public function __toString()
    {
        return $this->table_name.' '.json_encode($this->properties);
    }

    /**
     * Приведение свойства объекта к строке для записи в БД
     * @param mixed $value значение объекта
     * @param string $type название типа данных в строковом представлении
     * @return string строковое представление типа данных
     * @throws Exception генерируется если передаваемый тип неизвестен
     * Или если передан тип set, но $variable не массив
     */
    private function serializeProperty($value, $type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'integer':
            case 'int':
                return (string)intval($value);
            case 'string':
            case 'enum':
            case 'tinytext':
            case 'mediumtext':
            case 'varchar':
                return "'".$this->filterString($value)."'";
            case 'double':
                return (string)floatval($value);
            case 'set':
                if (is_array($value))
                    return "(".implode (", ", $value).")";
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
     * @return \app\models\DBTable объект, в рамках которого вызывался метод select
     * со сгенерированным началом запроса
     */
    public static function select($values = "*", $from = null)
    {
        $classname = get_called_class();
        $obj = new $classname();
        if (!is_array($values))
        {
            $obj->sql_query = "SELECT * ";
        }
        else
        {
            foreach ($values as &$value) 
            {
                $value = self::filterString($value);
            }
            $fields = implode(", ", $values);
            $obj->sql_query = "SELECT $fields ";
        }
            
        $obj->sql_query .= "FROM " . ($from === null ? $obj->table_name : $from);
        return $obj;
    }

    /**
     * Указание критерия для запроса (используется при вызове select или update)
     * @param string $where критерий запроса, который описывает блок WHERE
     * @return \app\models\DBTable объект, в рамках которого был дополнен запрос
     */
    public function where($where)
    {
        $this->sql_query .=  " WHERE $where ";
        return $this;
    }

    /**
     * Инициализация процедуры обновления данных в БД
     * @param array $values массив key=>value [string=>mixed]
     * @return \app\models\DBTable
     */
    public static function update($values)
    {
        $classname = get_called_class();
        $obj = new $classname();
        
        DBHelper::parseSchema($obj->table_name);
        $set = '';
        foreach ($values as $key => $value) 
        {
            if (strlen($set) > 0) {
                $set .= ', ';
            }
            $value = $obj->serializeProperty($value, 
                    DBHelper::getTypeName($obj->table_name, $key));
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
     * @return \app\models\DBTable
     */
    public function join($type , $table_name, $on = null)
    {
        $this->sql_query .= " $type JOIN $table_name ON $on";
        $this->sql_is_join = true;
        return $this;
    }
    
     /**
     * Установка лимита на выборку
     * @param integer $limit сколько
     * @param integer $offset смещение
     * @return \app\models\DBTable
     */
    public function limit($limit, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);
        $this->sql_query .= " LIMIT $offset, $limit ";
        return $this;
    }

    /**
     * Выполнение запроса, сгенерированного ранее для объекта
     * через вызовы методов select, update, where, join
     * @return mixed если данные есть то ArrayObject 
     * или app\models\DBTable в зависимости от количества найденных объектов или null 
     */
    public function execute()
    {
        $result_array = new \ArrayObject();
        
        $classname    = get_called_class();
        $table_name   = substr($classname, strrpos($classname, '\\') + 1);

        if (!DBHelper::isConnected()) {
            DBHelper::createConnection();
        }

        if ($this->sql_is_join !== false && DBHelper::getSchema($table_name) === null) {
            DBHelper::parseSchema($table_name);
        }

        $this->sql_query .= ";";

        /**
         * Получаем схему, если ее нет в скрипте
         */
        if (DBHelper::getSchema($table_name) === null)
        {
            DBHelper::parseSchema($table_name);
        }
        $db_result = DBHelper::$connection->query($this->sql_query);

        if (is_bool($db_result))
        {
            return $db_result === false ? null : $db_result;
        }           
            
        $ignore_schema = $this->sql_is_join;

        while ($row = mysqli_fetch_assoc($db_result))
        {
            $obj = new $classname();
            $obj->is_new = false;
            $obj->fillProperties($row, $ignore_schema);
            $result_array->append($obj);
        }
                
        $this->sql_query = "";
        $this->sql_is_join = false;
        
        return $result_array;
    }

    /**
     * Фильтрация строки, используемая для работы с БД и/или
     * вывода на страницу
     * @param type $value
     * @return type
     */
    private static function filterString($value)
    {
        return mysql_escape_string(htmlspecialchars($value));
    }
    
    /**
     * Представление объекта в виде массива $object['attribute'] = $value
     * @return array
     */
    public function asArray()
    {       
        $data = array();
        foreach ($this->properties as $key => $property) 
        {
            $data[$key] = $property['value'];
        }
        
        return count($data) > 0 ? $data : null;
    }
    
    /**
     * Представление массива в виде json строки
     * @return string
     */
    public function asJson()
    {
        return json_encode(self::asArray());
    }
}