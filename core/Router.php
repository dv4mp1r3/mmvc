<?php

namespace app\core;

use app\controllers;

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

    public function __construct($url = null)
    {
        if ($url === null) {
            $url = $_GET['u'];
        }

        if ($url === null) {
            header('Refresh: 0; url=/?u=home-index');
            exit();
        }

        $this->parseUrl($url);
        $this->controller = new $this->ctrlName();
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