<?php

return [
    'id' => 'app_tests',
    'components' => [
        'request' => [
            'enableCsrfValidation' => false, // disable csrf for ajax tests
        ],
    ],
];
