<?php

declare(strict_types=1);

namespace mmvc\core;

use mmvc\core\Router;

class Application
{
    /**
     * обработчик исключений для cli по умолчанию
     */
    const DEFAULT_EXCEPTION_HANDLER_CLI = 'mmvc\\core\\ExceptionHandler::doCliAppException';

    /**
     * обработчик исключений для веб по умолчанию
     */
    const DEFAULT_EXCEPTION_HANDLER_WEB = 'mmvc\\core\\ExceptionHandler::doWebAppException';

    /**
     *  обработчик ошибок по умолчанию
     */
    const DEFAULT_ERROR_HANDLER = 'mmvc\\core\\ExceptionHandler::doError';

    const CONFIG_KEY_DB = 'db';
    const CONFIG_KEY_USERS = 'users';
    const CONFIG_KEY_LOGPATH = 'logpath';
    const CONFIG_KEY_TIMEZONE = 'timezone';
    const CONFIG_KEY_ROUTE = 'route';
    const CONFIG_KEY_DEFAULT_ACTION = 'defaultAction';
    const CONFIG_KEY_ERROR_HANDLER = 'errorHandler';
    const CONFIG_KEY_EXCEPTION_HANDLER_WEB = 'exceptionHandlerWeb';
    const CONFIG_KEY_EXCEPTION_HANDLER_CLI = 'exceptionHandlerCli';

    const CONFIG_PARAM_DB_DRIVER = 'driver';
    const CONFIG_PARAM_DB_USERNAME = 'username';
    const CONFIG_PARAM_DB_PASSWORD = 'password';
    const CONFIG_PARAM_DB_HOST = 'host';
    const CONFIG_PARAM_DB_SCHEMA = 'schema';
    const CONFIG_PARAM_DEFAULT_CONTROLLER = 'controller';
    const CONFIG_PARAM_DEFAULT_ACTION = 'action';


    /**
     * @var array $config
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function run() : void
    {
        if (!defined('MMVC_DEBUG') || MMVC_DEBUG === false) {
            $handler = $this->getHandler(
                self::CONFIG_KEY_ERROR_HANDLER,
                self::DEFAULT_ERROR_HANDLER);
            set_error_handler($handler);
        }

        date_default_timezone_set($this->config[self::CONFIG_KEY_TIMEZONE]);

        $router = $this->initRouter();
        $router->route();
    }

    /**
     * Получение хендлера для обработки исключений/ошибок из конфига либо возвращение обработчика по умолчанию
     * @param string $configHandlerKey
     * @param string $defaultHandler
     * @return string
     */
    protected function getHandler(string $configHandlerKey, string $defaultHandler) : string
    {
        $handler = array_key_exists($configHandlerKey, $this->config) && !empty($this->config[$configHandlerKey])
            ? $this->config[$configHandlerKey]
            : $defaultHandler;
        return $handler;
    }

    /**
     * @return \mmvc\core\Router
     */
    protected function initRouter() : Router
    {
        if (php_sapi_name() === 'cli') {
            $handler = $this->getHandler(
                self::CONFIG_KEY_EXCEPTION_HANDLER_CLI,
                self::DEFAULT_EXCEPTION_HANDLER_CLI);
            set_exception_handler($handler);
            return new Router(Router::ROUTE_TYPE_CLI);
        } else {
            $handler = $this->getHandler(
                self::CONFIG_KEY_EXCEPTION_HANDLER_WEB,
                self::DEFAULT_EXCEPTION_HANDLER_WEB);
            set_exception_handler($handler);
            return new Router($this->config[self::CONFIG_KEY_ROUTE]);
        }
    }
}