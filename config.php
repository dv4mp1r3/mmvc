<?php

use mmvc\models\data\RDBHelper;
use mmvc\core\Router;

$config = ['db' => 
            [
                'driver' => RDBHelper::DB_TYPE_MYSQL,
                'username' => 'root',
                'password' => '',
                'host' => 'localhost',
                'schema' => 'mmvc_test',
            ],
            'users' => [
                'admin' =>
                [
                    'username' => 'admin',
                    'password' => '123',
                    'user_hash' => '24wejdslkfjsdfh2k3h5qwd',
                ],
            ],
            'logpath' => ROOT_DIR.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'main.log',
            'timezone' => 'Etc/GMT-3',
            'route' => Router::ROUTE_TYPE_DEFAULT,
    ];

