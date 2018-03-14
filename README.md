**Описание**

MMVC - micro mvc. Идея была проста: сделать свою реализацию mvc с максимум 2 потомками в дереве (т.е. не более 3 классов на ветку). При этом хотелось уметь как веб, так и cli, уметь в ORM и билдер запросов.

Этот проект был написан не использования на продакшене ради, а опыта для.

Проект вырос из одного тестового задания, которое требовало MVC с CRUD'ом одной таблицы. Задание самоусложнялось в целях обучения и в этого появилось то, что лежит сейчас в репозитории. К слову, некоторые демопроекты на основе этого имеются (например, [здесь](https://github.com/dv4mp1r3/ipinfo) и [здесь](https://github.com/dv4mp1r3/twitchwebm)), но исключительно чтобы показать работоспособность в лабораторных условиях. Даже после полного покрытия тестами использовать самописные фреймворки не в пет проектах это плохо и не надо так.

**Как оно устроено внутри?**
Все запросы перенаправляются в index.php, который инициализирует конфиг (по сути создается ассоциативный массив), далее управление передается объекту Router, задачей которого является инициализировать нужный контроллер и вызвать нужный метод.

**Иерархия классов**

![](https://dfvgbh.com/wp-content/uploads/2018/03/classes.png)

**Использование в веб-приложениях**

Допустим мы хотим обрабатывать ссылки /index.php?u=test-test

Для этого необходимо в каталоге controllers создать файл TestController.php и описать в нем класс TestController и метод actionTest:

```
<?php

namespace myapplicationnamespace\controllers;

use mmvc\core\AccessChecker;
use mmvc\controllers\WebController;

class GuestController extends WebController 
{
	public function __construct() {
		 //здесь можно определить, кто к каким экшенам может получать доступ
        $this->rules = [
            'test' => [
            AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
        ];
        parent::__construct();
    }

	public function actionTest() {
		return 'hello, world!';
	}
}
```

также можно включить ЧПУ, чтобы вместо /index.php?u=test-test использовать /test/test (смотрим пример конфига)

**Использование в консольных приложениях**

Все тоже самое, что и в случае веб-приложений, только наследоваться необходимо от класс CliController. [AccessChecker](https://github.com/dv4mp1r3/mmvc/blob/master/src/core/AccessChecker.php) не работает для консольных контроллеров. Пример консольного контроллера есть в самом проекте: [GenController](https://github.com/dv4mp1r3/mmvc/blob/master/src/controllers/GenController.php).

**Пример конфига**

```
$config = [
    // данные для работы с СУБД
    // обязка над PDO
    'db' =>
    [
        'driver' => RDBHelper::DB_TYPE_MYSQL,
        'username' => '',
        'password' => '',
        'host' => 'localhost',
        'schema' => 'mmvc_test',
    ],
    // не используется
    'users' => [
        'admin' =>
        [
            'username' => 'admin',
            'password' => '123',
            'user_hash' => '',
        ],
    ],
    // путь для сохранения логов
    'logpath' => MMVC_ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'main.log',
    // временная зона по умолчанию
    'timezone' => 'Etc/GMT-3',
    // тип ссылок
    'route' => Router::ROUTE_TYPE_FRIENDLY,
    // дефолтный маршрут
    // используется, например, при обращении к корневому каталогу без передачи параметров
    'defaultAction' => [
        // название контроллера (для генерации неймспейса и подгрузки нужного класса)
        'controller' => 'test',
        // название метода для вызова после подгрузки
        'action' => 'test'
    ],
];
```

