<?php

declare(strict_types=1);

namespace mmvc\core;

/**
 * Автоматическая подгрузка нужных файлов
 */
class Loader
{
    protected static string $baseNamespace = MMVC_VENDOR_NAMESPACE . '\\';
    protected static string $vendorBasePath = 'vendor\\dv4mp1r3\\' . MMVC_VENDOR_NAMESPACE . '\\src\\';

    public static function load(string $classname)
    {
        if (self::beginsAt($classname, self::$baseNamespace)) {
            // загружен класс фреймворка
            if (defined('MMVC_PROJECT_NAMESPACE') && MMVC_PROJECT_NAMESPACE === MMVC_VENDOR_NAMESPACE) {
                // заменяем mmvc на src (при запуске тестов)
                $classname = str_replace(MMVC_PROJECT_NAMESPACE, 'src', $classname);
            } else {
                // обрезаем mmvc\ в начале пути
                $classname = self::$vendorBasePath.substr($classname, strlen(self::$baseNamespace));
            }
        }
        else if (self::beginsAt($classname, MMVC_PROJECT_NAMESPACE))
        {
            $classname = substr($classname, strlen(MMVC_PROJECT_NAMESPACE.'\\'));
        }
        else
        {
            // если класс не относится к приложению или mmvc - передаем управление другому загрузчику
            // например, если это еще одна зависимость
            return;
        }
        $filename = MMVC_ROOT_DIR . DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
        require_once $filename;
    }

    private static function beginsAt(string $str, string $substr) : bool
    {
        return strpos($str, $substr) === 0;
    }
}
