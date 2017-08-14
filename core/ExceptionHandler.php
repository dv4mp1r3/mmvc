<?php namespace mmvc\core;

use mmvc\controllers\ErrorController;
use mmvc\controllers\CliController;

class ExceptionHandler {

    /**
     * Функция обработки исключений для веб-приложения
     * @param \Exception $ex
     */
    public static function doWebAppException($ex) {
        $err_ctrl = new ErrorController();
        if (DEBUG === false) {
            $err_ctrl->actionBase();
        } else {
            $err_ctrl->actionDetails($ex);
        }

        self::log($ex);
    }
    
    /**
     * Функция обработки исключений для cli-приложения
     * @param \Exception $ex
     */
    public static function doCliAppException($ex)
    {
        $err_ctrl = new CliController();
        $err_ctrl->printExceptionData($ex);
        
        self::log($ex);
    }

    /**
     * Функция обработки ошибок
     * @param int $errLevel тип ошибки
     * @param string $errMsg текст ошибки
     */
    public static function doError($errno, $errstr, $errfile, $errline) {
        self::log(null, [
            'level' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        ]);
    }

    /**
     * Функция логирования ошибок и исключений
     * Выполняется внезависимости от значения флага DEBUG
     * @param \Exception $ex
     * @param array $err информация об ошибке (массив ['level','message'])
     */
    protected static function log($ex = null, $err = null) {
        global $config;
        $log = fopen($config['logpath'], 'a+');
        if (!$log) {
            echo 'can not open logname (check access rights to log folder)';
        }

        fwrite($log, date("Y-m-d H:i:s"));
        fwrite($log, ' ==> ');

        if ($ex != null) {
            fwrite($log, $ex->getMessage());
            fwrite($log, ' at line "');
            fwrite($log, $ex->getLine());
            fwrite($log, '" of file ');
            fwrite($log, $ex->getFile());
            fwrite($log, "\r\nStack trace: \r\n");
            fwrite($log, $ex->getTraceAsString());
        }
        if ($err != null) {
            fwrite($log, self::parseErrorType($err['level']));
            fwrite($log, ' at "');
            fwrite($log, $err['line']);
            fwrite($log, "\"\r\n of file ");
            fwrite($log, $err['file']);
        }

        fwrite($log, "\r\n");
        fclose($log);
    }

    /**
     * Получение типа ошибки в виде строки
     * @param int $type
     * @return string
     */
    private static function parseErrorType($type) {
        $return = "";
        if ($type & E_ERROR)
            $return.='& E_ERROR ';
        if ($type & E_WARNING)
            $return.='& E_WARNING ';
        if ($type & E_PARSE)
            $return.='& E_PARSE ';
        if ($type & E_NOTICE)
            $return.='& E_NOTICE ';
        if ($type & E_CORE_ERROR)
            $return.='& E_CORE_ERROR ';
        if ($type & E_CORE_WARNING)
            $return.='& E_CORE_WARNING ';
        if ($type & E_COMPILE_ERROR)
            $return.='& E_COMPILE_ERROR ';
        if ($type & E_COMPILE_WARNING)
            $return.='& E_COMPILE_WARNING ';
        if ($type & E_USER_ERROR)
            $return.='& E_USER_ERROR ';
        if ($type & E_USER_WARNING)
            $return.='& E_USER_WARNING ';
        if ($type & E_USER_NOTICE)
            $return.='& E_USER_NOTICE ';
        if ($type & E_STRICT)
            $return.='& E_STRICT ';
        if ($type & E_RECOVERABLE_ERROR)
            $return.='& E_RECOVERABLE_ERROR ';
        if ($type & E_DEPRECATED)
            $return.='& E_DEPRECATED ';
        if ($type & E_USER_DEPRECATED)
            $return.='& E_USER_DEPRECATED ';
        return substr($return, 2);
    }

}
