<?php
$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'geoip' => [
            'class' => 'dpodium\yii2\geoip\components\CGeoIP',
            'mode'  => 'STANDARD',  // Choose MEMORY_CACHE or STANDARD mode
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                    // manual map custom translation domains and files
                    // 'fileMap' => [
                    //     'app'  => 'app.php',
                    // ],
                ],
                'mail*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'security' => [
            'class' => 'common\components\CSecurity',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mainUrlManager' => [
            'class' => 'common\components\web\CUrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => $params['publicUrl'],
            'rules' => [
                '<lang:\w{2}-\w{2}|\w{2}>'                         => 'site/index',
                ''                                                 => 'site/index',
                '<lang:\w{2}-\w{2}|\w{2}>/logout'                  => 'site/logout',
                'logout'                                           => 'site/logout',
                '<lang:\w{2}-\w{2}|\w{2}>/entrance'                => 'site/entrance',
                'entrance'                                         => 'site/entrance',
                '<lang:\w{2}-\w{2}|\w{2}>/account-activation'      => 'site/activation',
                'account-activation'                               => 'site/activation',
                '<lang:\w{2}-\w{2}|\w{2}>/forgotten-password'      => 'site/forgotten-password',
                'forgotten-password'                               => 'site/forgotten-password',
                '<lang:\w{2}-\w{2}|\w{2}>/reset-password'          => 'site/reset-password',
                'reset-password'                                   => 'site/reset-password',
                '<lang:\w{2}-\w{2}|\w{2}>/change-email'            => 'site/change-email',
                'change-email'                                     => 'site/change-email',
                '<lang:\w{2}-\w{2}|\w{2}>/<slug:[\w\-]+>'          => 'preview/view',
                '<slug:[\w\-]+>'                                   => 'preview/view',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>' => 'projects/view',
                'admin/projects/<id:\d+>'                          => 'projects/view',
            ],
        ],
    ],
    'params' => $params,
];
