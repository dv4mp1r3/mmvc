<?php

namespace mmvc\models;

class BaseView extends BaseModel 
{
    protected $templatePath;
    
    /**
     * Массив со значениями перед передачей во вьюху
     * очищается после вызова render
     * @var array 
     */
    protected $vars;

    /**
     * 
     * @param string $templatePath
     */
    public function __construct($templatePath) {
        $this->templatePath = $templatePath;
        $this->vars = [];
        parent::__construct();
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
     * Добавление переменной в шаблон
     * @param array $data ассоциативный массив для привязки ко вьюхе
     * @param string $name ключ для массива
     */
    public function appendVariables($data, $name = 'data')
    {
        $this->vars[$name] = $data;        
    }
    
    /**
     * Выдача шаблона
     * @param string $view имя вьюшки, которую надо отдать клиенту
     * @param boolean $$isFullPath использование полного пути во $view
     * @see \mmvc\controllers\ErrorController
     */
    public function render($view, $isFullPath = false)
    {
        ob_start();
        extract($this->vars, EXTR_OVERWRITE);
        $this->vars = [];
        if ($isFullPath) {
            require_once $view;
        } else {
            require_once MMVC_ROOT_DIR . "/views/{$this->templatePath}/$view.php";
        }
        $content = ob_get_contents();
        ob_end_flush();
        return $content;        
    }
    
}
