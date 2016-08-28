<?php

namespace app\controllers;
use app\core\ViewTemplate;

class BaseController 
{
    /**
     * массив правил
     * @var array 
     */
    public $rules;
    
    /**
     * Имя контроллера без постфикса Controller
     * @var string 
     */
    protected $name;
    
    public function __construct() {
        $classname = get_called_class();
        $tmp = substr($classname, strrpos($classname, '\\') + 1);
        $this->name = substr($tmp, 0, strpos($tmp, 'Controller'));
    }


    public function render($view)
    {
        global  $config;
        $class = $config['template']['class'];

        $template = new $class($this->name, $view);
        $template->doHtml();
    } 
    
    public function getName()
    {
        return $this->name;
    }

}