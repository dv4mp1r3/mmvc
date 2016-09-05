<?php

namespace app\core;

use app\core\ExceptionHandler;

class Router
{
    /**
     * контроллер для передачи ему управления
     * @var BaseController 
     */
    protected $controller;

    /**
     * Имя action, который должен быть вызван
     * @var string 
     */
    protected $action;

    /**
     * Параметры для action
     * @var array
     */
    protected $params;

    /**
     *
     * @var string 
     */
    protected $ctrlName;

    /**
     * обработчик исключений
     * @var app\core\ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * Конструктор роутера (обработка ссылок, выдача нужной страницы в зависимости от url)
     * @param app\core\ExceptionHandler $exh - обработчик исключений
     * @param string $url
     * @throws Exception
     */
    public function __construct($exh = null, $url = null)
    {
        if ($exh === null) {
            $exh = new ExceptionHandler();
        } else if (!($exh instanceof ExceptionHandler)) {
            throw new \Exception('First parameter of Router->construct() must be'
            .'istance of app\\core\\ExceptionHandler');
        }

        $this->exceptionHandler = $exh;

        if ($url === null) {
            $url = $_GET['u'];
        }

        try {
            if ($url === null) {
                throw new \Exception('$url is not defined');
            }

            throw new \Exception('Test Excpetion');

            $this->parseUrl($url);
            $this->controller = new $this->ctrlName();
        } catch (\Exception $ex) {
            $this->exceptionHandler->doException($ex);
        }
    }

    protected function parseUrl($url)
    {
        $delemiter      = strpos($url, '-');
        $ctrl           = htmlspecialchars(substr($url, 0, $delemiter));
        $this->action   = htmlspecialchars(substr($url, $delemiter + 1));
        $this->ctrlName = 'app\\controllers\\'.ucfirst($ctrl).'Controller';
    }

    protected function callAction()
    {
        call_user_func(array($this->controller, 'action'.ucfirst($this->action)));
    }

    public function route()
    {
        if (AccessChecker::checkAccess($this->controller, $this->action)) {
            $this->callAction();
        }
    }
}