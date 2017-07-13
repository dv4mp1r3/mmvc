<?php namespace app\models;

use app\models\data\RDBRecord;

/**
 * Модель загруженного в сервис видео
 * @property integer id
 * @property string $url
 * @property string $user_id
 */
class Video extends RDBRecord
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

    /**
     * Возвращение уникального идентификатора видео 
     * https://www.youtube.com/watch?v=pFBUh3hKKDg -> pFBUh3hKKDg
     * @return string
     */
    public function getVideoId()
    {
        $pos = strpos($this->url, "v=");
        return substr($this->url, $pos + 2);
    }
}
