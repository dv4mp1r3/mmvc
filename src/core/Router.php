<?php
namespace mmvc\core;

use mmvc\controllers\BaseController;
use mmvc\controllers\WebController;
use mmvc\controllers\CliController;

class Router
{

    const ROUTE_TYPE_DEFAULT  = 0, // по умолчанию обрабатывается $_GET['u']
          //domain.zone/controller/action/var1/val1/val2/var2
          ROUTE_TYPE_FRIENDLY = 1, // ЧПУ
          // тоже что и ROUTE_TYPE_DEFAULT, только данные берутся из $argv
          ROUTE_TYPE_CLI      = 2; // консольная версия

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
     * @var Config
     */
    private $config;

    /**
     * Конструктор роутера (обработка ссылок, выдача нужной страницы в зависимости от url)
     * @param integer $routeType
     * @throws Exception
     */
    public function __construct($routeType = Router::ROUTE_TYPE_DEFAULT, Config $config)
    {
        $this->config = $config;
        switch ($routeType) {
            case Router::ROUTE_TYPE_DEFAULT:
                $this->parseUrl($_GET['u']);
                break;
            case Router::ROUTE_TYPE_FRIENDLY:
                $this->parseUrlFriendly();
                break;
            case Router::ROUTE_TYPE_CLI:
                if (empty($_SERVER['argv'])) {
                    throw new \Exception('Empty cli-arguments but router type defined as CLI');
                }
                $this->parseUrl($_SERVER['argv'][1]);
                break;
            default:
                throw new \Exception("Unknown route type $routeType");
        }

        $this->controller = new $this->ctrlName($this->config);
    }

    /**
     * Обработка урл вида index.php?u=ctrlName-view
     * @param string $url значение $_GET['u']
     */
    protected function parseUrl($url)
    {
        if ($url === null) {
            throw new \Exception('$url is not defined');
        }

        $delemiter = strpos($url, '-');
        $ctrl = htmlspecialchars(substr($url, 0, $delemiter));
        $this->action = htmlspecialchars(substr($url, $delemiter + 1));

        $expectedFilename = MMVC_ROOT_DIR.
            DIRECTORY_SEPARATOR.'controllers'.
            DIRECTORY_SEPARATOR.ucfirst($ctrl).'Controller.php';

        if (defined('MMVC_PROJECT_NAMESPACE') && file_exists($expectedFilename)) {
            $this->ctrlName = MMVC_PROJECT_NAMESPACE . '\\controllers\\' . ucfirst($ctrl) . 'Controller';
        } else {
            $this->ctrlName = 'mmvc\\controllers\\' . ucfirst($ctrl) . 'Controller';
        }
    }

    /**
     * Обработка урл вида index/ctrlName/view/paramName/paramValue...
     * строка разбивается на пары (параметр-значение)
     * @throws \Exception если количество пар = 0
     */
    protected function parseUrlFriendly()
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, '/', MMVC_ROOT_DIR);
        $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);

        $dir_arr = explode('/', $dir);
        $url_arr = explode('/', $url);

        $result = [];

        foreach ($url_arr as $param) {
            if (in_array($param, $dir_arr) || strlen($param) === 0)
                continue;
            array_push($result, $param);
        }

        $count = count($result);

        if ($count == 0) {
            $result = $this->getDefaultAction();
        }

        switch ($result[0]) {
            case 'error':
            case 'gen':
            case 'cli':
                $this->ctrlName = 'mmvc\\controllers\\' . ucfirst($result[0]) . 'Controller';
                break;
            default:
                if (!defined('MMVC_PROJECT_NAMESPACE')) {
                    throw new \Exception("constant MMVC_PROJECT_NAMESPACE undefined. Can not route {$result[0]}->{$result[1]}");
                }
                $this->ctrlName = MMVC_PROJECT_NAMESPACE . '\\controllers\\' . ucfirst($result[0]) . 'Controller';
                break;
        }
        $this->action = ucfirst($result[1]);

        if ($count > 2) {
            for ($i = 2; $i < $count; $i++) {
                if (isset($result[$i]) && isset($result[$i + 1])) {
                    $_REQUEST[$result[$i]] = $result[$i + 1];
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
        if ($this->action === null) {
            throw new \Exception('Router->action is null');
        }
        if ($this->controller === null &&
            !($this->controller instanceof \mmvc\controllers\BaseController)) {
            throw new \Exception('Router->controller is null or not instance of BaseController');
        }
        return call_user_func(array($this->controller, 'action' . ucfirst($this->action)));
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
    
    protected function getDefaultAction()
    {
        global $config;
        if (empty($config['defaultAction'])) {
            throw new \Exception('parseUrlFriendly error (param count = 0)');
        }
        $result = [
            $config['defaultAction']['controller'],
            $config['defaultAction']['action'],
        ];
        return $result;
    }
}
