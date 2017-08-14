<?php namespace mmvc\core;

use mmvc\controllers\BaseController;

class AccessChecker
{

    const USER_ANONYMOUS = '?';
    const USER_ALL = '*';
    const RULE_DENIED = 'denied';
    const RULE_GRANTED = 'granted';

    /**
     * Проверка доступа к action для текущего пользователя
     * @param BaseController $controller
     * @param string $actionName
     */
    public static function checkAccess($controller, $actionName)
    {
        $rules = $controller->rules;
        if (empty($rules[$actionName])) {
            return true;
        }

        $access_granted = false;
        $access_denied = false;

        $username = self::getUsername();

        if (isset($rules[$actionName][self::RULE_GRANTED])) {
            $access_granted = self::accessResult($rules[$actionName][self::RULE_GRANTED], $username);
        }

        if (isset($rules[$actionName][self::RULE_DENIED])) {
            $access_denied = self::accessResult($rules[$actionName][self::RULE_DENIED], $username);
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
            return self::USER_ANONYMOUS;
        }

        $user_hash = $_COOKIE['user_hash'];
        global $config;

        foreach ($config['users'] as $key => $value) {
            if (in_array('user_hash', $value)) {
                return $key;
            }
        }
        // Если ничего не найдено - возвращаем guest
        return self::USER_ANONYMOUS;
    }

    /**
     * Проверка существования правила в контроллере
     * @param array $rules
     * @param string $username
     * @return boolean результат
     */
    protected static function accessResult($rules, $username)
    {
        if ($rules === self::USER_ALL) {
            return true;
        }

        if (is_array($rules) &&
            (in_array($username, $rules) ||
            in_array(self::USER_ALL, $rules)
            )
        ) {
            return true;
        }

        return false;
    }
}
