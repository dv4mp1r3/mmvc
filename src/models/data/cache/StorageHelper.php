<?php

namespace mmvc\models\data\cache;

/**
 * Хелпер для получения инстансов классов для работы с кешем
 */
class StorageHelper extends \mmvc\models\BaseModel {
    
    /**
     * Возможные ключи опций в конфиге
     */
    const OPTION_INSTANCE_TYPE = 'type';
    const OPTION_HOST = 'host';
    const OPTION_PORT = 'port';    
    const OPTION_AUTH_LOGIN = 'login';
    const OPTION_AUTH_PASSWORD = 'password';
    
    /**
     * not implemented
     */
    const OPTION_REDIS_CONNECTION_TYPE = 'connType';
    
    const INSTANCE_REDIS = 0;
    const INSTANCE_MEMCACHED = 1;
    
    /**
     * Возврат инстанса класса 
     * @param string $storageConfig
     * @param callable $callBack
     * @throws \Exception
     */
    public static function getInstance($storageConfig = 'default', $callBack = null)
    {               
        $storageConfig = static::getStorageConfig($storageConfig);        
        $storage = null;
        switch ($storageConfig['type']) {
            case self::INSTANCE_REDIS:
                $storage = new \Redis();
                break;
            case self::INSTANCE_MEMCACHED:
                $storage = new \Memcached();
                break;
            default :
                throw new \Exception("Unknown storage type: {$storageConfig['type']}");
        }
        
        if (gettype($callBack) === 'callable')
        {
            $callBack($storage);
        }
        
        if (!$storage->connect($storageConfig['host'], $storageConfig['port']))
        {
            throw new \Exception("Connection to cache storage $storageConfig not possible");
        }
        return $storage;      
    }
    
    /**
     * Получение конфига для кеша
     * @global array $config
     * @param string $storageConfig
     * @return array
     * @throws \Exception
     */
    private static function getStorageConfig($storageConfig)
    {
        global $config;
        if (empty($config['cache']))
        {
            throw new \Exception("Empty 'cache' section in config");
        }
        if (empty($config['cache'][$storageConfig]))
        {
            throw new \Exception("Empty '$storageConfig' section in config");
        }
        return $config['cache'][$storageConfig];
    }
}
