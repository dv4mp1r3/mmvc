<?php namespace mmvc\controllers;

use mmvc\controllers\WebController;

class ErrorController extends WebController
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
     * Вывод детальной информации об исключении
     * @param \Exception $ex
     */
    public function actionDetails($ex)
    {
        $this->appendVariable('exceptionMessage', $ex->getMessage());
        $stackTrace = '<div>' .
            str_replace("\n", "</div><div>", $ex->getTraceAsString()) .
            '</div';
        $this->appendVariable('stackTrace', $stackTrace);
        $this->appendVariable('www_root', $this->getHttpRootPath());
        $this->render('details');
    }

    /**
     * Вывод детальной информации об ошибке
     */
    public function actionBase()
    {
        $this->render('base');
    }
    
    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     */
    public function render($view)
    {
        $this->smarty->display(__DIR__."/../views/$this->name/$view.tpl");
    }
}