<?php

namespace app\models;

class BaseModel
{
    /**
     * 
     * @var string 
     */
    protected $objectName;

    public function getName()
    {
        return substr(
                get_called_class(), 
                strrpos($this->getClassName(), '\\') + 1
                );
    }
    
    public function getClassName()
    {
        return get_called_class();
    }
    
    public function __construct() {
        $this->objectName = $this->getName();
    }
}