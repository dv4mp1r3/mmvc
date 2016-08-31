<?php

namespace app\core;

use app\controllers\BaseController;

class AccessChecker
{

    /**
     * Проверка доступа к action для текущего пользователя
     * @param BaseController $controller
     * @param string $actionName
     */
    public static function checkAccess($controller, $actionName)
    {
        $rules = $controller->rules;
        if (!isset($rules[$actionName])) {
            return true;
        }

        $access_granted = false;
        $access_denied  = false;

        $username = self::getUsername();

        if (isset($rules[$actionName]['granted'])) {
            $access_granted = self::accessResult($rules[$actionName]['granted'],
                    $username);
        }

        if (isset($rules[$actionName]['denied'])) {
            $access_denied = self::accessResult($rules[$actionName]['denied'],
                    $username);
        }

        if ($access_granted && $access_denied) {
            $ctrlName = $controller->getName();
            throw new \LogicException("Access denied and granted at same time for action $ctrlName-$actionName");
        }

        // Правило явно не определено - разрешить доступ
        return $access_granted && !$access_denied;
    }

    /**
     * Получение имени текущего пользователя
     * @return string
     */
    public static function getUsername()
    {
        if (!isset($_COOKIE['user_hash'])) {
            return '?';
        }

        $user_hash = $_COOKIE['user_hash'];
        global $config;

        foreach ($config['users'] as $key => $value) {
            if (in_array('user_hash', $value)) {
                return $key;
            }
        }
        // Если ничего не найдено - возвращаем guest
        return '?';
    }

    /**
     * Проверка существования правила в контроллере
     * @param array $rules
     * @param string $username
     * @return boolean результат
     */
    protected static function accessResult($rules, $username)
    {
        if ($rules === '*') {
            return true;
        }

        if (is_array($rules) &&
            (in_array($username, $rules) ||
            in_array('*', $rules)
            )
        ) {
            return true;
        }

        return false;
    }
}