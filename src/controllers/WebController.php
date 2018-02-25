<?php namespace mmvc\controllers;

use mmvc\core\AccessChecker;
use mmvc\controllers\BaseController;

class WebController extends BaseController
{
    const RULE_TYPE_ACCESS_DENIED = AccessChecker::RULE_DENIED;
    const RULE_TYPE_ACCESS_GRANTED = AccessChecker::RULE_GRANTED;
    
    /**
     * Массив со значениями перед передачей во вьюху
     * очищается после вызова render
     * @var array 
     */
    protected $vars;
    
    public function __construct()
    {
        parent::__construct();
        $this->vars = [];
    }
    
    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     * @param boolean $$isFullPath использование полного пути во $view
     * @see \mmvc\controllers\ErrorController
     */
    public function render($view, $isFullPath = false)
    {
        $viewFolderName = lcfirst($this->name);
        ob_start();
        extract($this->vars, EXTR_OVERWRITE);
        $this->vars = [];
        if ($isFullPath) {
            require_once $view;
        } else {
            require_once MMVC_ROOT_DIR . "/views/$viewFolderName/$view.php";
        }
        return ob_get_contents();
    }

    /**
     * Добавление переменной в шаблон
     * @param string $name имя
     * @param mixed $value
     * @param integer 
     */
    public function appendVariable($name, $value)
    {
        $this->vars[$name] = $value;        
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
        foreach ($params as $key => $value) {
            $this->appendVariable($key, $value);
        }
        $content = $this->render($template, true);
        ob_clean();
        return $content;
    }

    /**
     * Получение доменного имени 
     * @return string
     */
    public function getHttpRootPath()
    {
        return '//' . $_SERVER['HTTP_HOST'] . str_replace("/index.php", "", $_SERVER['PHP_SELF']);
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
