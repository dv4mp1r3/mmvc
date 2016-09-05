<?php

define('DEBUG', false);
define('ROOT_DIR', dirname(__FILE__));

$config = ['db' => 
            [
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
            'template' => [
                'file' => ROOT_DIR.'/assets/template/master.php',
            ],
    ];

