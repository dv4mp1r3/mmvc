<?php namespace app\controllers;

use app\controllers\BaseController;

class CliController extends BaseController
{    
    protected function getInputParameter($name)
    {
        return $_SERVER['argv'][(int)$name];
    }
}
