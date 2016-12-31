<?php

namespace app\core;

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
     * Конструктор роутера (обработка ссылок, выдача нужной страницы в зависимости от url)
     * @param string $url
     * @throws Exception
     */
    public function __construct($url = null)
    {
        if ($url === null) {
            $url = $_GET['u'];
        }

        if ($url === null) {
            throw new \Exception('$url is not defined');
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
        if ($this->action === null)
        {
            throw new \Exception('Router->action is null');
        }
        if ($this->controller === null &&
            !($this->controller instanceof \app\controllers\BaseController))
        {
            throw new \Exception('Router->controller is null or not instance of BaseController');
        }
        call_user_func(array($this->controller, 'action'.ucfirst($this->action)));
    }

    public function route()
    {
        if (AccessChecker::checkAccess($this->controller, $this->action)) {
            $this->callAction();
        }
    }
}