<?php namespace app\controllers;

use app\core\Loader;
use Smarty;

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
    protected $smarty;

    public function __construct($masterPage = null)
    {
        $classname = get_called_class();
        $tmp = substr($classname, strrpos($classname, '\\') + 1);
        $this->name = substr($tmp, 0, strpos($tmp, 'Controller'));
        $this->smarty = new Smarty();
        if ($masterPage !== null) {
            $this->masterPage = $masterPage;
        }
    }

    /**
     * Выдача шаблона клиенту
     * @param string $view имя вьюшки, которую надо отдать клиенту
     */
    public function render($view)
    {
        $this->smarty->display("views/$this->name/$view.tpl");
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
}
