<?php namespace mmvc\models;

class BaseModel
{

    /**
     * 
     * @var string 
     */
    protected $modelName;

    public function getName()
    {
        return substr(
            get_called_class(), strrpos($this->getClassName(), '\\') + 1
        );
    }

    public function getClassName()
    {
        return get_called_class();
    }

    public function __construct()
    {
        $this->modelName = $this->getName();
    }
}
