<?php namespace mmvc\controllers;

use Smarty;
use mmvc\core\AccessChecker;
use mmvc\controllers\BaseController;

class WebController extends BaseController
{
    const RULE_TYPE_ACCESS_DENIED = AccessChecker::RULE_DENIED;
    const RULE_TYPE_ACCESS_GRANTED = AccessChecker::RULE_GRANTED;
    
    protected $smarty;
    
    public function __construct()
    {
        parent::__construct();
        $this->smarty = new Smarty();
    }
    
    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     */
    public function render($view)
    {
        $this->smarty->display(MMVC_ROOT_DIR."/views/$this->name/$view.tpl");
    }

    /**
     * Добавление переменной в шаблон
     * @param string $name имя
     * @param mixed $value
     */
    public function appendVariable($name, $value)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * Получение части html-контента
     * рекомендуется использовать для ajax-запросов
     * когда, например, нужно получить готовые div с данными
     * @param string $template путь к шаблону в папке views
     * @param array $params массив параметров ($key => $value) для шаблона
     * @return string
     */
    public function getHtmlContent($template, $params)
    {
        $sm = new Smarty();
        foreach ($params as $key => $value) {
            $sm->assign($key, $value);
        }

        return $sm->fetch($template);
    }

    /**
     * Получение доменного имени 
     * @return string
     */
    public function getHttpRootPath()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . str_replace("/index.php", "", $_SERVER['PHP_SELF']);
    }

    protected function getInput($name, $filterType = null, $inputType = null)
    {
        if ($inputType === null)
        {
            return filter_input(INPUT_REQUEST, $name);
        }
        
        if ($inputType && $filterType)
        {
            return filter_input($inputType, $name, $filterType);
        }
        
        throw new \Exception('Bad parameters');
    }
}
