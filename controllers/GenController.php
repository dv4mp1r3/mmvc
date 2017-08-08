<?php

namespace app\controllers;

class GenController extends CliController
{
    const PARAM_VALUE_MODELNAME = 2;
    
    
    public function __construct()
    {       
        parent::__construct();
        
    }
    
    public function actionModel()
    {
        $modelName = $this->getInput(self::PARAM_VALUE_MODELNAME, FILTER_SANITIZE_STRING);
        echo "Model name $modelName\n";
        
    }
}
