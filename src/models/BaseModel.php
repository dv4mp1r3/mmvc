<?php namespace mmvc\models;

class BaseModel
{

    /**
     * 
     * @var string 
     */
    protected $modelName;

    /**
     * Возврат имени класса без неймспейса
     * Пример: BaseModel
     * @return string
     */
    public function getName()
    {
        return substr(
            get_called_class(), strrpos($this->getClassName(), '\\') + 1
        );
    }

    /**
     * Возврат имени класса вместе с неймспейсом
     * Пример: mmvc\models\BaseModel
     * @return string
     */
    public function getClassName()
    {
        return get_called_class();
    }

    public function __construct()
    {
        $this->modelName = $this->getName();
    }
}
