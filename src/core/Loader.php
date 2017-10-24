<?php namespace mmvc\core;

/**
 * Автоматическая подгрузка нужных файлов
 */
class Loader
{
    protected static $baseNamespace = MMVC_VENDOR_NAMESPACE.'\\';
    protected static $vendorBasePath = 'vendor\\dv4mp1r3\\'.MMVC_VENDOR_NAMESPACE.'\\src\\';
    //protected static $required_files = array();

    public static function load($classname)
    {
        if (self::beginsAt($classname, self::$baseNamespace)) {
            // загружен класс фреймворка
            // обрезаем mmvc\ в начале пути
            $classname = self::$vendorBasePath.substr($classname, strlen(self::$baseNamespace));
        }
        else if (self::beginsAt($classname, MMVC_PROJECT_NAMESPACE))
        {
            $classname = substr($classname, strlen(MMVC_PROJECT_NAMESPACE.'\\'));
        }
        $filename = MMVC_ROOT_DIR . DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';

        //if (!in_array($filename, self::$required_files)) {
            require_once $filename;
            //array_push(self::$required_files, $filename);
        //}
    }

    private static function beginsAt($str, $substr)
    {
        return strpos($str, $substr) === 0;
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
