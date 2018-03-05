<?php

namespace mmvc\models\data\cache;

/**
 * Интерфейс для работы с кешем
 */
interface Cached {
    
    /**
     * Генерация уникального для объекта ключа
     */
    public function calculateKey(); 
    
    /**
     * Сохранение данных в кеш
     */
    public function saveCache();
    
    /**
     * Получение данных из кеша
     */
    public function loadFromCache();
    
    /**
     * Сборка данных для кеша
     */
    public function collectData();
    
}
