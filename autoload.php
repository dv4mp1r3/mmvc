<?php

define('MMVC_VENDOR_NAMESPACE', 'mmvc');

if (!defined('MMVC_PROJECT_NAMESPACE'))
{
    // путь к проекту в случае запуска тестов
    define('MMVC_ROOT_DIR', dirname(__FILE__));
    define('MMVC_PROJECT_NAMESPACE', 'mmvc');
} else {
    // путь к проекту, который использует mmvc как зависимость
    define('MMVC_ROOT_DIR', dirname(__FILE__).'/../../../');
}
require_once __DIR__.'/src/core/Loader.php';
spl_autoload_register('mmvc\\core\\Loader::load');
