<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'enableCsrfCookie' => false,
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
        ],
        'errorHandler' => [
            'class' => 'api\components\ApiErrorHandler',
        ],
        'user' => [
            'identityClass'   => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession'   => false,
            'loginUrl'        => null,
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
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'rules' => [
                // Users
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'users',
                    'patterns' => [
                        'POST login'    => 'login',
                        'POST register' => 'register',
                        'PUT  update'   => 'update',
                    ],
                ],

                // Projects
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'projects',
                ],

                // Versions
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'versions',
                ],

                // Screens
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'screens',
                ],

                // Screen comments
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'comments' => 'screen-comments'
                    ],
                ],

                // Preview
                [
                    'pluralize' => false,
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'previews',
                    'patterns' => [
                        'GET,HEAD {slug}' => 'view',
                        'POST {slug}'     => 'add-comment',
                    ],
                    'tokens' => [
                        '{slug}' => '<slug:[\w\-\_]+>',
                    ],
                ],
            ],
        ]
    ],
    'params' => $params,
];
