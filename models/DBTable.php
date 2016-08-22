<?php

namespace app\models;

use app\models\DBHelper;

require_once dirname(__FILE__).'/DBHelper.php';

class DBTable extends BaseModel {
    // prop = ['name' => ['is_dirty' => false, 'schema' => 'integer', 'value' => 1]]
    private $properties;
    private $is_new;
    private $table_name;
    private $first_load = true;
   
    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param integer $id PK записи для выгрузки существующий (опционально)
     */
    public function __construct($id = null) {
        $classname = get_called_class();
        $this->table_name = substr($classname, strrpos($classname, '\\') + 1);        
        
        $this->is_new = ($id === null);
        if (!$this->is_new)
        {           
            if (DBHelper::isConnected())
            {
                DBHelper::createConnection();
            }
            $db_result = DBHelper::$connection->
                query("SELECT * FROM $this->table_name WHERE id=$id"); 
            
            $this->fillProperties(mysqli_fetch_array($db_result));            
            $this->getSchema($this->table_name);
        }
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
        $props = ''; $values = '';
        $delemiter = ', ';
        foreach ($this->properties as $key => $data) 
        {
            if (!isset($data['value']) || $this->isPrimaryKey($key))
                continue;
            
            if (strlen($props) > 0)
            {
                $props .= $delemiter;
            }
            $props .= "`$key`";
            
            if (strlen($values) > 0)
            {
                $values .= $delemiter;
            }
            $this->properties[$key]['is_dirty'] = false;
            $values .= "'".htmlspecialchars($data['value'])."'";
        }        
        $q = "INSERT INTO $this->table_name ($props) VALUES ($values);";
        return $q;
    }
    
    /**
     * Создание запроса для обновления данных для существующей записи
     * Вызывается при сохранении (метод save())
     * @return string готовый запрос UPDATE $table_name SET (values) WHERE id=$id;
     */
    private function buildUpdateQuerty()
    {
        $values = '';
        $new_values = 0;
        foreach ($this->properties as $key => $data)
        {          
            if (!$this->isDirtyProperty($key) || $this->isPrimaryKey($key))
            {
                continue;
            }
            
            if (strlen($values) > 0)
                $values .= ', ';
            $value = htmlspecialchars($data['value']);
            $values .= "`$key`='$value'";
            
            $this->properties[$key]['is_dirty'] = false;
            $new_values++;
        }
        if ($new_values === 0)
        {
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
        foreach ($this->properties as $key => $data) 
        {
            if ($data['flags'] & MYSQLI_PRI_KEY_FLAG)
            {
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
        if ($this->is_new)
        {
            $query = $this->buildInsertQuery();
        }
        else
        {
            $query = $this->buildUpdateQuerty();
        }
                
        if (DBHelper::$connection === null)
        {
            DBHelper::createConnection();
        }
        $result = DBHelper::$connection->query($query);

        if (!$result)
            throw new \Exception('MySQL error: '.DBHelper::$connection->error);
        
        if ($this->is_new)
            $this->id = DBHelper::$connection->insert_id;
        //var_dump($query);
        $this->is_new = false;
    }
    
    public function __get($name) 
    {
        return $this->properties[$name]['value'];
    }
    
    public function __set($name, $value) 
    {
        $this->properties[$name]['value'] = $value;
        if (!$this->first_load && $name !== 'id')
            $this->properties[$name]['is_dirty'] = true;
     
    }

    /**
     * Выборка всех записей из таблицы по критерию
     * @param string $criteria заключительная часть запроса к базе, следующая после WHERE
     * @return \ArrayObject 
     */
    public static function findByCriteria($criteria)
    {
        $result_array = new \ArrayObject();
        $classname = get_called_class();
        $table_name = substr($classname, strrpos($classname, '\\') + 1);
        if (!DBHelper::isConnected())
            DBHelper::createConnection();
        
        if (DBHelper::getSchema($table_name) !== null)
            DBHelper::parseSchema ($table_name);
        
        $db_result = DBHelper::$connection->query("SELECT * FROM $table_name WHERE $criteria"); 
        
        while ($row = mysqli_fetch_assoc($db_result)) 
        {
            $review = new Review();
            $review->fillProperties($row);
            $result_array->append($review);
        }
        return $result_array;       
    }
    
    protected function fillProperties($props)
    {        
        foreach ($props as $key => $value) 
        {
            if (DBHelper::isPropertyExists($this->table_name, $key))
            {
                $this->__set($key, $value);
                $this->properties[$key]['is_dirty'] = false;
            }
        }
    }  
}
