<?php

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
                'class' => 'app\\core\\ViewTemplate',
            ],
    ];

