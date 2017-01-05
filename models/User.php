<?php

namespace app\models;

/**
 * Модель загруженного в сервис видео
 * @property integer id
 * @property string $name
 */
class User extends DBTable
{
    /**
     * Загрузка атрибутов модели из $_POST
     */
    public function loadFromPost()
    {
        unset($_POST['action']);
        $schema = $this->getSchema();
        foreach ($_POST as $key => $value) {
            if (isset($schema[$key])) {
                $this->__set($key, htmlspecialchars($value));
            }
        }
    }

    public function save()
    {
        $this->is_changed_by_admin = true;
        parent::save();
    }
}
