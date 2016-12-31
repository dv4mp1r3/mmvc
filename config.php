<?php

define('DEBUG', true);
define('ROOT_DIR', dirname(__FILE__));

$config = ['db' => 
            [
                'username' => 'root',
                'password' => '',
                'host' => 'localhost',
                'schema' => 'mmvc',
            ],
            'users' => [
                'admin' =>
                [
                    'username' => 'admin',
                    'password' => '123',
                    'user_hash' => '24wejdslkfjsdfh2k3h5qwd',
                ], 
            ],
            'template' => [
                'file' => ROOT_DIR.'/assets/template/master.php',
            ],
            'logpath' => ROOT_DIR.PATH_SEPARATOR.'log'.PATH_SEPARATOR.'main.log',
            'timezone' => 'Etc/GMT-3',
    ];

