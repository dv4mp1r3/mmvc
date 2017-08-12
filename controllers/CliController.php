<?php namespace app\controllers;

use app\controllers\BaseController;

class CliController extends BaseController
{    
    protected function getInput($name, $filterType = null, $inputType = null)
    {
        $value = $_SERVER['argv'][$name];
        return filter_var($value, $filterType);
    }
    
    protected function printLine($message)
    {
        echo "$message\n";
    }
}
