<?php namespace mmvc\controllers;

use mmvc\core\AccessChecker;
use mmvc\controllers\BaseController;
use mmvc\models\BaseView;

class WebController extends BaseController
{
    const RULE_TYPE_ACCESS_DENIED = AccessChecker::RULE_DENIED;
    const RULE_TYPE_ACCESS_GRANTED = AccessChecker::RULE_GRANTED;
    
    /**
     *
     * @var BaseView 
     */
    protected $view;
    
    public function __construct()
    {
        parent::__construct();
        $this->view = new BaseView(lcfirst($this->name));
    }
    
    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     * @param boolean $$isFullPath использование полного пути во $view
     * @see \mmvc\controllers\ErrorController
     */
    public function render($view, $data = null, $isFullPath = false)
    {
        if (is_array($data))
        {
            $this->view->appendVariables($data);
        }        
        return $this->view->render($view, $isFullPath);
    }

    /**
     * Добавление переменной в шаблон
     * @param string $name имя
     * @param mixed $value
     */
    public function appendVariable($name, $value)
    {
        $this->view->appendVariable($name, $value);
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
        return $this->render($template, $params, true);
    }

    /**
     * Получение доменного имени 
     * @return string
     */
    public function getHttpRootPath()
    {
        if (!empty ($_SERVER['PHP_SELF']))
        {
            return '//' . $_SERVER['HTTP_HOST'] . str_replace("/index.php", "", $_SERVER['PHP_SELF']);
        }

        return '//' . $_SERVER['HTTP_HOST'] . str_replace("/index.php", "", $_SERVER['DOCUMENT_URI']);
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
