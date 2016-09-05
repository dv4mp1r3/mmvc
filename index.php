<?php
namespace app;

use app\core\Router;

require_once 'config.php';
require_once ROOT_DIR.'/core/Loader.php';

spl_autoload_register('app\\core\\Loader::load');

$view_variable = [];

$router = new Router();
$router->route();
