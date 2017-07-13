<?php namespace app\models\data;

use app\models\BaseModel;

class StoredObject extends BaseModel
{

    const PROPERTY_ATTRIBUTE_IS_DIRTY = 'is_dirty';
    const PROPERTY_ATTRIBUTE_VALUE = 'value';

    // prop = ['name' => ['is_dirty' => false, 'schema' => 'integer', 'value' => 1]]
    protected $properties;

    /**
     * Новая ли это запись?
     * true если это новый инстанс объекта, полученный не при помощи select()
     * необходимо выставить в false после помещения в хранилище
     * @var boolean 
     */
    protected $isNew;

    /**
     * Название сущности, с которой ассоциируется объект
     * Для RDBMS это чаще всего имя таблицы
     * @var string 
     */
    protected $objectName;
    protected $firstLoad = true;

    public function __construct($objectName = null)
    {
        parent::__construct();

        if ($objectName !== null)
            $this->objectName = $objectName;
        else
            $this->objectName = $this->modelName;
    }

    /**
     * Представление объекта в виде массива $object['attribute'] = $value
     * @return array
     */
    public function asArray()
    {
        $data = array();
        foreach ($this->properties as $key => $property) {
            $data[$key] = $property[StoredObject::PROPERTY_ATTRIBUTE_VALUE];
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

    public function __get($name)
    {

        if (empty($this->properties[$name])) {
            $msg = "Trying to access on unexisting property $name of " . $this->getName();
            throw new \Exception($msg);
        }

        return $this->properties[$name][StoredObject::PROPERTY_ATTRIBUTE_VALUE];
    }

    public function __set($name, $value)
    {
        $this->properties[$name][StoredObject::PROPERTY_ATTRIBUTE_VALUE] = $value;
        if (!$this->firstLoad) {
            $this->properties[$name][StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] = true;
        }
    }

    /**
     * Проверка, было ли свойство модели модифицировано после извлечения из БД
     * @param string $name
     * @return boolean true если свойство было модифицировано, но не сохранено в БД
     */
    protected function isDirtyProperty($name)
    {
        $data = $this->properties[$name];
        return isset($data[StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY]) && $data[StoredObject::PROPERTY_ATTRIBUTE_IS_DIRTY] === true;
    }

    public function save()
    {
        $this->isNew = false;
    }
}
