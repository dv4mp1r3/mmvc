<?php

namespace app\controllers;

global $base_path;

class BaseController 
{
    protected $rules;
    protected $name;
    
    public function __construct() {
        $classname = get_called_class();
        $tmp = substr($classname, strrpos($classname, '\\') + 1);
        $this->name = substr($tmp, 0, strpos($tmp, 'Controller'));
    }
    
    protected function renderHeader()
    {
        echo '<head>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="css/my.css">
        <script src="js/jquery.js"></script>       
        <script src="js/bootstrap.js"></script>
        <script src="js/my.js"></script>
        </head>';
    }
    
    protected function renderDoctype()
    {
        echo '<!DOCTYPE html>';
    }


    public function render($view)
    {
        $this->renderDoctype();
        echo '<html>';
        $this->renderHeader();     
        require_once dirname(__FILE__).'/../views/'.$this->name.'/'.$view.'.php';
        echo '</html>';
    } 

}