<?php

namespace app\core;
/**
 * Автоматическая подгрузка нужных файлов
 */
class Loader
{

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
        require_once $filename;
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
}