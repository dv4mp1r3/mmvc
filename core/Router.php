<?php

namespace app\core;

class Router
{
    const ROUTE_TYPE_DEFAULT = 0, // по умолчанию обрабатывается $_GET['u']
            ROUTE_TYPE_FRIENDLY = 1; //ЧПУ
    
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
    public function __construct($route_type = Router::ROUTE_TYPE_DEFAULT)
    {
        switch ($route_type) {
            case Router::ROUTE_TYPE_DEFAULT:
                $url = $_GET['u'];
                if ($url === null) {
                    throw new \Exception('$url is not defined');
                }
                $this->parseUrl($url);
                break;
            case Router::ROUTE_TYPE_FRIENDLY:
                $this->parseUrlFriendly();
                break;
            default:
                throw new \Exception("Unknown route type $route_type");
        }
        
        $this->controller = new $this->ctrlName();
    }

    /**
     * Обработка урл вида index.php?u=ctrlName-view
     * @param string $url значение $_GET['u']
     */
    protected function parseUrl($url)
    {
        $delemiter      = strpos($url, '-');
        $ctrl           = htmlspecialchars(substr($url, 0, $delemiter));
        $this->action   = htmlspecialchars(substr($url, $delemiter + 1));
        $this->ctrlName = 'app\\controllers\\'.ucfirst($ctrl).'Controller';
    }
    
    /**
     * Обработка урл вида index/ctrlName/view/paramName/paramValue...
     * строка разбивается на пары (параметр-значение)
     * @throws \Exception если количество пар = 0
     */
    protected function parseUrlFriendly()
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, '/', ROOT_DIR);
        $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
        
        $dir_arr = explode('/', $dir);
        $url_arr = explode('/', $url);
        
        $result = [];
        
        foreach ($url_arr as $param) 
        {           
            if (in_array($param, $dir_arr) || strlen($param) === 0)
                continue;
            array_push($result, $param);
        }
        
        $count = count($result);
        
        if ($count == 0)
            throw new \Exception ('parseUrlFriendly error (param count = 0)');
        
        $this->ctrlName = 'app\\controllers\\'.ucfirst($result[0]).'Controller';
        $this->action = ucfirst($result[1]);
        
        if ($count > 2)
        {
            for ($i = 2; $i < $count; $i++)
            {
                if (isset($result[$i]) && isset($result[$i+1]))
                {
                    $_REQUEST[$result[$i]] = $result[$i+1];
                    $i++;
                }
            }
        }
    }

    /**
     * Обработка и вызов нужного действия для контроллера
     * @return string результат выполнения действия (шаблон страницы, json для ajax
     * и т.д.)
     * @throws \Exception выбрасывается если не найдено действие или контроллер
     */
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
        return call_user_func(array($this->controller, 'action'.ucfirst($this->action)));
    }

    /**
     * Передача управления контроллеру
     * после обработки урла в конструкторе
     */
    public function route()
    {
        if (AccessChecker::checkAccess($this->controller, $this->action)) {
            echo $this->callAction();
        }
    }
}