<?php
namespace app;

use app\core\Router;

define('DEBUG', false);
define('ROOT_DIR', dirname(__FILE__));

require_once 'config.php';
require_once ROOT_DIR.'/core/Loader.php';

spl_autoload_register('app\\core\\Loader::load');

$view_variable = [];

$router = new Router();
$router->route();
