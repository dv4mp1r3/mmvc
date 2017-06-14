<?php

namespace app\models\data;

abstract class AbstractDatabaseTable {
    
    /**
     * Инстанс объекта для работы с БД
     * @var \app\models\db\AbstractDatabaseHelper 
     */
    private $dbHelper;
    
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
// TODO реализация подгрузки объектов через коннект
//            if (DBHelper::isConnected()) {
//                DBHelper::createConnection();
//            }
//            $db_result = DBHelper::$connection->
//                query("SELECT * FROM $this->table_name WHERE id=$id");
//            
//            DBHelper::parseSchema($this->table_name);
//            $this->fillProperties(mysqli_fetch_array($db_result));     
        }
        $this->sql_query = "";
        $this->sql_is_join = false;
        $this->first_load = false;
    }
}
