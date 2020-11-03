<?php
return [
    'id' => 'presentator-api',
    'controllerNamespace' => 'presentator\api\controllers',
    'bootstrap' => [
        [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
                'application/xml'  => \yii\web\Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'bg-BG',
                'de-DE',
                'fr-FR',
                'it-IT',
                'nl-NL',
                'pt-BR',
            ],
        ],
    ],
    'components' => [
        'request' => [
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json'    => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ]
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
        ],
        'errorHandler' => [
            'class' => 'presentator\api\rest\ErrorHandler',
        ],
        'user' => [
            'identityClass'   => 'presentator\api\models\User',
            'enableAutoLogin' => false,
            'enableSession'   => false,
            'loginUrl'        => null,
        ],
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
            ],
            'rules' => [
                // Users
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'users',
                    'patterns' => [
                        'GET,HEAD auth-clients'       => 'list-auth-clients',
                        'POST     auth-clients'       => 'authorize-auth-client',
                        'POST login'                  => 'login',
                        'POST register'               => 'register',
                        'POST activate'               => 'activate',
                        'POST request-password-reset' => 'request-password-reset',
                        'POST confirm-password-reset' => 'confirm-password-reset',
                        'POST request-email-change'   => 'request-email-change',
                        'POST confirm-email-change'   => 'confirm-email-change',
                        'POST feedback'               => 'feedback',
                        'POST refresh'                => 'refresh',
                        'GET,HEAD'                    => 'index',
                        'POST'                        => 'create',
                        'PUT,PATCH {id}'              => 'update',
                        'GET,HEAD {id}'               => 'view',
                        'DELETE {id}'                 => 'delete',
                        'auth-clients'                => 'options',
                        'login'                       => 'options',
                        'register'                    => 'options',
                        'activate'                    => 'options',
                        'request-password-reset'      => 'options',
                        'confirm-password-reset'      => 'options',
                        'request-email-change'        => 'options',
                        'confirm-email-change'        => 'options',
                        'feedback'                    => 'options',
                        'refresh'                     => 'options',
                        '{id}'                        => 'options',
                        ''                            => 'options',
                    ],
                ],
                // Projects
                [
                    'pluralize'     => false,
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'projects',
                    'extraPatterns' => [
                        'GET,HEAD {id}/collaborators'    => 'list-collaborators',
                        'GET,HEAD {id}/users/search'     => 'search-users',
                        'GET,HEAD {id}/users'            => 'list-users',
                        'POST {id}/users/<userId:\d+>'   => 'link-user',
                        'DELETE {id}/users/<userId:\d+>' => 'unlink-user',
                        '{id}/collaborators'             => 'options',
                        '{id}/users/search'              => 'options',
                        '{id}/users'                     => 'options',
                        '{id}/users/<userId:\d+>'        => 'options',
                    ],
                ],
                // Prototypes
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'prototypes',
                    'extraPatterns' => [
                        'POST {id}/duplicate' => 'duplicate',
                        '{id}/duplicate'      => 'options',
                    ],
                ],
                // Project links
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'project-links',
                    'extraPatterns' => [
                        'GET,HEAD accessed' => 'accessed',
                        'POST {id}/share'   => 'share',
                        'accessed'          => 'options',
                        '{id}/share'        => 'options',
                    ],
                ],
                // Guideline sections
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'guideline-sections',
                ],
                // Guideline assets
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'guideline-assets',
                ],
                // Screen comments
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'screen-comments',
                    'extraPatterns' => [
                        'GET,HEAD unread'     => 'list-unread',
                        'PUT,PATCH {id}/read' => 'read',
                        'unread'              => 'options',
                        '{id}/read'           => 'options',
                    ],
                ],
                // Screens
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'screens',
                    'extraPatterns' => [
                        'PUT,PATCH bulk-update' => 'bulk-update',
                        'bulk-update'           => 'options',
                    ],
                ],
                // Hotspot templates
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'hotspot-templates',
                    'extraPatterns' => [
                        'GET,HEAD {id}/screens'              => 'list-screens',
                        'POST {id}/screens/<screenId:\d+>'   => 'link-screen',
                        'DELETE {id}/screens/<screenId:\d+>' => 'unlink-screen',
                        '{id}/screens'                       => 'options',
                        '{id}/screens/<screenId:\d+>'        => 'options',
                    ],
                ],
                // Hotspots
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'hotspots',
                ],
                // Previews
                [
                    'pluralize'  => false,
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'previews',
                    'patterns' => [
                        'POST'                           => 'authorize',
                        'GET,HEAD'                       => 'index',
                        'GET,HEAD prototypes/{id}'       => 'prototype',
                        'GET,HEAD assets'                => 'assets',
                        'GET,HEAD screen-comments'       => 'list-screen-comments',
                        'POST screen-comments'           => 'create-screen-comment',
                        'PUT,PATCH screen-comments/{id}' => 'update-screen-comment',
                        'POST report'                    => 'report',
                        ''                               => 'options',
                        'prototypes/{id}'                => 'options',
                        'assets'                         => 'options',
                        'screen-comments'                => 'options',
                        'screen-comments/{id}'           => 'options',
                        'report'                         => 'options',
                    ],
                ],
            ],
        ]
    ],
];
