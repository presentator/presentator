<?php

return [
    'id' => 'app_tests',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'request' => [
            'enableCsrfValidation' => false, // disable csrf for ajax tests
        ],
    ],
];
