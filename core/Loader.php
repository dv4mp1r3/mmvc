<?php

namespace app\core;

use app\controllers;
use app\models;
use app\views;
/**
 * Автоматическая подгрузка нужных файлов
 */
class Loader {
    
    public static function load($classname)
    {
        $baseNamespace = 'app\\';
        
        if (self::beginsAt($classname, $baseNamespace))
        {
            // загружен класс фреймворка
            // обрезаем app\ в начале пути
            $classname = substr($classname, strlen($baseNamespace));
        }
        $filename = ROOT_DIR.'/'.  str_replace('\\', '/', $classname).'.php';
        require_once $filename;
    }
    
    private static function beginsAt($str, $substr)
    {
        return strpos($str, $substr) === 0;
    }
    
}
