<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-main',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'request' => [
            'csrfParam' => '_csrf-app-main',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-main', 'httpOnly' => true],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'facebook' => [
                    'class'        => 'yii\authclient\clients\Facebook',
                    'clientId'     => $params['facebookAuth']['clientId'],
                    'clientSecret' => $params['facebookAuth']['clientSecret'],
                ],
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the app
            'name' => 'app-main',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'class' => 'common\components\web\CUrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<lang:\w{2}-\w{2}|\w{2}>'                    => 'site/index',
                ''                                            => 'site/index',
                '<lang:\w{2}-\w{2}|\w{2}>/logout'             => 'site/logout',
                'logout'                                      => 'site/logout',
                '<lang:\w{2}-\w{2}|\w{2}>/entrance'           => 'site/entrance',
                'entrance'                                    => 'site/entrance',
                '<lang:\w{2}-\w{2}|\w{2}>/account-activation' => 'site/activation',
                'account-activation'                          => 'site/activation',
                '<lang:\w{2}-\w{2}|\w{2}>/forgotten-password' => 'site/forgotten-password',
                'forgotten-password'                          => 'site/forgotten-password',
                '<lang:\w{2}-\w{2}|\w{2}>/reset-password'     => 'site/reset-password',
                'reset-password'                              => 'site/reset-password',

                // Users
                '<lang:\w{2}-\w{2}|\w{2}>/admin/account'                    => 'users/settings',
                'admin/account'                                             => 'users/settings',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/account/temp-avatar-upload' => 'users/ajax-temp-avatar-upload',
                'admin/account/temp-avatar-upload'                          => 'users/ajax-temp-avatar-upload',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/account/avatar-save'        => 'users/ajax-avatar-save',
                'admin/account/avatar-save'                                 => 'users/ajax-avatar-save',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/account/avatar-delete'      => 'users/ajax-avatar-delete',
                'admin/account/avatar-delete'                               => 'users/ajax-avatar-delete',

                // Projects
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects'                                => 'projects/index',
                'admin/projects'                                                         => 'projects/index',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>'                       => 'projects/view',
                'admin/projects/<id:\d+>'                                                => 'projects/view',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>/delete'                => 'projects/delete',
                'admin/projects/<id:\d+>/delete'                                         => 'projects/delete',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>/ajax-get-update-form'  => 'projects/ajax-get-update-form',
                'admin/projects/<id:\d+>/ajax-get-update-form'                           => 'projects/ajax-get-update-form',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>/ajax-save-update-form' => 'projects/ajax-save-update-form',
                'admin/projects/<id:\d+>/ajax-save-update-form'                          => 'projects/ajax-save-update-form',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>/ajax-share'            => 'projects/ajax-share',
                'admin/projects/<id:\d+>/ajax-share'                                     => 'projects/ajax-share',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/<id:\d+>/ajax-search-users'     => 'projects/ajax-search-users',
                'admin/projects/<id:\d+>/ajax-search-users'                              => 'projects/ajax-search-users',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/ajax-load-more'                 => 'projects/ajax-load-more',
                'admin/projects/ajax-load-more'                                          => 'projects/ajax-load-more',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/ajax-search-projects'           => 'projects/ajax-search-projects',
                'admin/projects/ajax-search-projects'                                    => 'projects/ajax-search-projects',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/ajax-admin-admin'               => 'projects/ajax-admin-admin',
                'admin/projects/ajax-admin-admin'                                        => 'projects/ajax-admin-admin',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/projects/ajax-remove-admin'              => 'projects/ajax-remove-admin',
                'admin/projects/ajax-remove-admin'                                       => 'projects/ajax-remove-admin',

                // Versions
                '<lang:\w{2}-\w{2}|\w{2}>/admin/versions/ajax-create'             => 'versions/ajax-create',
                'admin/versions/ajax-create'                                      => 'versions/ajax-create',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/versions/ajax-delete'             => 'versions/ajax-delete',
                'admin/versions/ajax-delete'                                      => 'versions/ajax-delete',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/versions/ajax-get-screens-slider' => 'versions/ajax-get-screens-slider',
                'admin/versions/ajax-get-screens-slider'                          => 'versions/ajax-get-screens-slider',

                // Screens
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-upload'             => 'screens/ajax-upload',
                'admin/screens/ajax-upload'                                      => 'screens/ajax-upload',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-delete'             => 'screens/ajax-delete',
                'admin/screens/ajax-delete'                                      => 'screens/ajax-delete',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-get-settings-form'  => 'screens/ajax-get-settings-form',
                'admin/screens/ajax-get-settings-form'                           => 'screens/ajax-get-settings-form',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-save-settings-form' => 'screens/ajax-save-settings-form',
                'admin/screens/ajax-save-settings-form'                          => 'screens/ajax-save-settings-form',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-reorder'            => 'screens/ajax-reorder',
                'admin/screens/ajax-reorder'                                     => 'screens/ajax-reorder',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-save-hotspots'      => 'screens/ajax-save-hotspots',
                'admin/screens/ajax-save-hotspots'                               => 'screens/ajax-save-hotspots',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screens/ajax-move-screens'       => 'screens/ajax-move-screens',
                'admin/screens/ajax-move-screens'                                => 'screens/ajax-move-screens',

                // Screen comments
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screen-comments/ajax-create'       => 'screen-comments/ajax-create',
                'admin/screen-comments/ajax-create'                                => 'screen-comments/ajax-create',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screen-comments/ajax-delete'       => 'screen-comments/ajax-delete',
                'admin/screen-comments/ajax-delete'                                => 'screen-comments/ajax-delete',
                '<lang:\w{2}-\w{2}|\w{2}>/admin/screen-comments/ajax-get-comments' => 'screen-comments/ajax-get-comments',
                'admin/screen-comments/ajax-get-comments'                          => 'screen-comments/ajax-get-comments',

                // Preview
                '/<lang:\w{2}-\w{2}|\w{2}>/<slug:[\w\-]+>'                   => 'preview/view',
                '<slug:[\w\-]+>'                                             => 'preview/view',
                '<lang:\w{2}-\w{2}|\w{2}>/<slug:[\w\-]+>/ajax-invoke-access' => 'preview/ajax-invoke-access',
                '<slug:[\w\-]+>/ajax-invoke-access'                          => 'preview/ajax-invoke-access',
            ],
        ],

    ],
    'params' => $params,
];
