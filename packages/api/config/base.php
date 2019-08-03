<?php
$params = array_merge(
    require(__DIR__ . '/params.php'),
    (file_exists(__DIR__ . '/params-local.php') ? require(__DIR__ . '/params-local.php') : [])
);

return [
    'id' => 'presentator',
    'name' => 'Presentator',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower'           => '@vendor/bower-asset',
        '@npm'             => '@vendor/npm-asset',
        '@tests'           => '@app/tests',
        '@presentator/api' => '@app', // fix PSR-4 console error because current yii2 autoloader is based on path aliases
    ],
    'bootstrap'  => ['log'],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'security' => [
            'class' => 'presentator\api\base\Security',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class'          => 'yii\i18n\PhpMessageSource',
                    'basePath'       => '@app/messages',
                    'sourceLanguage' => 'en-US',
                ],
                'mail*' => [
                    'class'          => 'yii\i18n\PhpMessageSource',
                    'basePath'       => '@app/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'fs' => [
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path'  => '@app/web/storage',
        ],
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                // valid clients:
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                ],
                'twitter' => [
                    'class' => 'yii\authclient\clients\TwitterOAuth2',
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                ],
                'github' => [
                    'class' => 'yii\authclient\clients\GitHub',
                ],
                'gitlab' => [
                    'class' => 'presentator\api\authclients\GitLab',
                ],
            ],
        ],

        // Activates Firebase Cloud Firestore comments notifications
        // ---
        // 'firestore' => [
        //     'class'      => 'presentator\api\base\Firestore',
        //     'authConfig' => '/path/to/firebase-auth.json',
        //     'projectId'  => 'presentator-v2',
        // ],
    ],
    'params' => $params,
];
