<?php

namespace app\core;
/**
 * Автоматическая подгрузка нужных файлов
 */
class Loader
{
    protected static $required_files = array();

    public static function load($classname)
    {
        $baseNamespace = 'app\\';

        if (self::beginsAt($classname, $baseNamespace)) {
            // загружен класс фреймворка
            // обрезаем app\ в начале пути
            $classname = substr($classname, strlen($baseNamespace));
        }
        $filename = ROOT_DIR.DIRECTORY_SEPARATOR.
            str_replace('\\', DIRECTORY_SEPARATOR, $classname).'.php';

        if (!in_array($filename, self::$required_files))
        {
            require_once $filename;
            array_push(self::$required_files, $filename);
        }       
    }

    private static function beginsAt($str, $substr)
    {
        return strpos($str, $substr) === 0;
    }

    public static function loadView($masterPath, $ctrlName, $viewName )
    {
        define('MMVC_CTRL_NAME', $ctrlName);
        define('MMVC_CTRL_VIEW', $viewName);

        require_once $masterPath;
    }

    /**
     * Возврат всех загруженных скриптов на момент вызова функции
     * @return array required_files
     */
    public static function getReqiredFiles()
    {
        return self::$required_files;
    }
}