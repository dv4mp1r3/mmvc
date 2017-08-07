<?php namespace app\controllers;

use app\core\Loader;

abstract class BaseController
{
    
    const INPUT_PARAMETER_CLI = 0,
          INPUT_PARAMETER_GET = 1,
          INPUT_PARAMETER_POST = 2,
          INPUT_PARAMETER_REQUEST = 3;
    
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
   
    public function __construct()
    {
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
    
    /**
     * Получение входящего параметра по его типу (источник) и имени
     * @param integer $type
     * @param mixed $name имя параметра или число для консольных контроллеров
     * @return mixed
     * @throws \Exception
     */
    protected abstract function getInputParameter($name, $type = null);
}
