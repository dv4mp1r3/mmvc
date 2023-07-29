<?php namespace mmvc\controllers;

use mmvc\core\Config;

class ErrorController extends WebController
{

    public function __construct(Config $config, array $args = [])
    {
        parent::__construct($config, $args);
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
        $name = lcfirst($this->name);
        parent::render(__DIR__."/../views/$name/details.php", null, true);
    }

    /**
     * Вывод детальной информации об ошибке
     */
    public function actionBase()
    {
        $name = lcfirst($this->name);
        parent::render(__DIR__."/../views/$name/base.php", null, true);
    }
}
