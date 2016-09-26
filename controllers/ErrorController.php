<?php

namespace app\controllers;

class ErrorController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->rules = [
            'base' => [
                'granted' => '*',
            ],
            'details' => [
                'granted' => '*',
            ],
        ];
    }
    /**
     *
     * @param \Exception $ex
     */
    public function actionDetails($ex)
    {
        global $view_variable;
        $view_variable = $ex;
        $this->render('details');
    }

    public function actionBase()
    {
        $this->render('base');
    }
}