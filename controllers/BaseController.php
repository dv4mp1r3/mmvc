<?php

namespace app\controllers;

use app\core\Loader;

class BaseController
{
    /**
     * массив правил
     * @var array 
     */
    public $rules;

    /**
     * Имя контроллера без постфикса Controller
     * @var string 
     */
    protected $name;

    /**
     * Путь к Мастер-странице шаблона (задается в конструкторе либо в методе setMasterPage)
     * Если не задан, путь берется из $config['template']['file']
     * @var string
     */
    protected $masterPage;

    public function __construct($masterPage = null)
    {
        $classname  = get_called_class();
        $tmp        = substr($classname, strrpos($classname, '\\') + 1);
        $this->name = substr($tmp, 0, strpos($tmp, 'Controller'));

        if ($masterPage === null) {
            global $config;
            $this->masterPage = $config['template']['file'];
        }
    }

    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     */
    public function render($view)
    {
        Loader::loadView($this->masterPage, $this->name, $view);
    }

    /**
     * Получение имени контроллера
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Задание новой Мастер-страницы шаблона для контроллера
     * @param string $filename полный путь к файлу
     * @throws \Exception при отсутствии файла по заданному пути
     */
    public function setMasterPage($filename)
    {
        if (is_file($filename)) {
            $this->masterPage = $filename;
        }

        throw new \Exception("File not found ($filename)");
    }
}