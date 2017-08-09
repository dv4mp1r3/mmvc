<?php
namespace app;

use app\core\Router;

require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'core/Loader.php';

date_default_timezone_set($config['timezone']);

spl_autoload_register('app\\core\\Loader::load');
if (!defined('DEBUG') || DEBUG === false) {
    set_error_handler('app\\core\\ExceptionHandler::doError');
}
set_exception_handler('app\\core\\ExceptionHandler::doException');

$router = null;

if (php_sapi_name() === 'cli') {
    $router = new Router(Router::ROUTE_TYPE_CLI);
} else {
    session_start();
    $router = new Router(Router::ROUTE_TYPE_DEFAULT);
}

$router->route();
