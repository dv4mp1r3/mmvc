<?php
namespace app;

use app\core\Router;

define('DEBUG', true);
define('ROOT_DIR', dirname(__FILE__));

require_once 'vendor/autoload.php';
require_once 'core/Loader.php';

spl_autoload_register('app\\core\\Loader::load');

require_once 'config.php';

if (!defined('DEBUG') || DEBUG === false) {
    set_error_handler('app\\core\\ExceptionHandler::doError');
}

date_default_timezone_set($config['timezone']);

$router = null;

if (php_sapi_name() === 'cli') {
    set_exception_handler('app\\core\\ExceptionHandler::doCliAppException');
    $router = new Router(Router::ROUTE_TYPE_CLI);
} else {
    set_exception_handler('app\\core\\ExceptionHandler::doWebAppException');
    session_start();
    $router = new Router(Router::ROUTE_TYPE_FRIENDLY);
}

$router->route();
