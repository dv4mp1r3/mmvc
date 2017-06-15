<?php

namespace app\models\data;

abstract class AbstractDatabaseTable {
    
    /**
     * Инстанс объекта для работы с БД
     * @var \app\models\db\AbstractDatabaseHelper 
     */
    protected $dbHelper;
    
    // prop = ['name' => ['is_dirty' => false, 'schema' => 'integer', 'value' => 1]]
    protected $properties;
    protected $is_new;
    protected $object_name;
    protected $first_load = true;
    
    /**
     * Создание новой записи либо выбор существующей из таблицы
     * @param integer $id PK записи для выгрузки существующий (опционально)
     */
    public function __construct($id = null)
    {
        $classname        = get_called_class();
        $this->object_name = substr($classname, strrpos($classname, '\\') + 1);

        $this->is_new = ($id === null);
        if (!$this->is_new) {
            $this->initNew($id);
        }
    }
    
// TODO реализация подгрузки объектов через коннект
//            if (DBHelper::isConnected()) {
//                DBHelper::createConnection();
//            }
//            $db_result = DBHelper::$connection->
//                query("SELECT * FROM $this->table_name WHERE id=$id");
//            
//            DBHelper::parseSchema($this->table_name);
//            $this->fillProperties(mysqli_fetch_array($db_result)); 
    protected abstract function initNew($id);
    
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
}
