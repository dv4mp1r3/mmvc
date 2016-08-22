<?php
namespace app\models;

require_once dirname(__FILE__).'/DBTable.php';

/** 
 * Модель отзыва
 * @property integer id 
 * @property string $email 
 * @property string $name
 * @property string $text
 * @property string $avatar
 * @property boolean $is_approved
 * @property boolean $is_changed_by_admin
 */
class Review extends DBTable 
{
    /**
     * Загрузка атрибутов модели из $_POST
     */
    public function loadFromPost()
    {
        unset($_POST['action']);
        foreach ($_POST as $key => $value) 
        {
            if (isset(self::$schema[$key]))
            {
                $this->__set($key, htmlspecialchars($value));
            }           
        }
    }
}
