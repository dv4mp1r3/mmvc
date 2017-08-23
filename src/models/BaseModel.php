<?php namespace mmvc\models;

class BaseModel
{

    /**
     * 
     * @var string 
     */
    protected $ctrlName;

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
        $this->ctrlName = $this->getName();
    }
}
