<?php
namespace app;

use app\core\Router;

require_once 'config.php';
require_once '/vendor/autoload.php';
require_once ROOT_DIR.'/core/Loader.php';

date_default_timezone_set($config['timezone']);

spl_autoload_register('app\\core\\Loader::load');
//set_error_handler('app\\core\\ExceptionHandler::doError');
set_exception_handler('app\\core\\ExceptionHandler::doException');

session_start();
 $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
        var_dump($url);
        die;
$router = new Router();
$router->route();
