<?php
return [
    'id' => 'presentator-tests',
    'language' => 'en-US',
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'fs' => 'creocoder\flysystem\NullFilesystem',
    ],
];
