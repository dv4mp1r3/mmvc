<?php namespace mmvc\controllers;

use mmvc\core\Config;
use mmvc\core\Loader;

abstract class BaseController
{
    
    const INPUT_PARAMETER_CLI = INPUT_SERVER,
          INPUT_PARAMETER_GET = INPUT_GET,
          INPUT_PARAMETER_POST = INPUT_POST,
          INPUT_PARAMETER_REQUEST = INPUT_REQUEST;
    
    /**
     * Правило, определяющее, откуда брать входящие параметры для action
     * Возможные значения INPUT_PARAMETER_*
     */
    const RULE_TYPE_INPUT = 'input';

    /**
     * массив правил ключ - значение, где ключ - название action без префикса
     * маленькими буквами, значение - массив правил.
     * Правила - массив ключ-значение где ключ - константа, определенная в 
     * контроллере с именем RULE_TYPE_* и значением.
     * Допустимые значения свои для каждого правила
     * Пример:
     * $this->rules = [
            'index' => [
                self::RULE_TYPE_ACCESS_GRANTED => AccessChecker::USER_ALL,
                self::RULE_TYPE_INPUT => self::INPUT_PARAMETER_GET,
            ],
            'info' => [
                self::RULE_TYPE_ACCESS_GRANTED => AccessChecker::USER_ALL,
                self::RULE_TYPE_INPUT => self::INPUT_PARAMETER_GET,
            ],
        ];
     * Массив должен быть определен в конструкторе контроллера
     * Дополнение: не используется в потомках CliController
     * @var array 
     */
    public $rules;

    /**
     * Имя контроллера без постфикса Controller
     * @var string 
     */
    protected $name;

    /**
     * @var Config
     */
    private $config;
   
    public function __construct(Config $config)
    {
        $this->config = $config;
        $classname = get_called_class();
        $tmp = substr($classname, strrpos($classname, '\\') + 1);
        $this->name = substr($tmp, 0, strpos($tmp, 'Controller'));        
    }
    
    /**
     * Получение имени контроллера
     * @return string
     */
    public function getName()
    {        
        return $this->name;
    }

    protected function getConfig() : Config {
        return $this->config;
    }
    
    /**
     * Получение входящего параметра по его типу (источник) и имени
     * @param mixed $name имя параметра или число для консольных контроллеров
     * @param integer $filterType тип фильтра входящего параметра (FILTER_*)
     * @param integer $inputType источник для обработки входящего параметра (INPUT_PARAMETER_*)
     * null для  консольных контроллеров
     * @return mixed
     * @throws \Exception
     */
    protected abstract function getInput($name, $filterType = null, $inputType = null);
}
