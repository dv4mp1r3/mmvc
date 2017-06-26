<?php

define('DEBUG', true);
define('ROOT_DIR', dirname(__FILE__));

$config = ['db' => 
            [
                'driver' => 'mysql',
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
    ];

