<?php
namespace app;

use app\core\Router;

require_once 'config.php';
require_once ROOT_DIR.'/core/Loader.php';

spl_autoload_register('app\\core\\Loader::load');
set_error_handler('app\\core\\ExceptionHandler::doError');
set_exception_handler('app\\core\\ExceptionHandler::doException');

$view_variable = [];

$router = new Router();
$router->route();
