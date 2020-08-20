<?php
$params = array_merge(
    require(__DIR__ . '/../vendor/presentator/api/config/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'params'      => $params,
    'runtimePath' => (__DIR__ . '/../runtime'),
    'vendorPath'  => (__DIR__ . '/../vendor'),
    'components'  => [
        'fs' => [
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path'  => (__DIR__ . '/../web/storage'),
        ],
        'db' => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=localhost;dbname=presentator',
            'username' => 'presentator',
            'password' => '',
            'charset'  => 'utf8mb4',
            // schema cache
            'enableSchemaCache'   => true,
            'schemaCacheDuration' => 604800, // 1 week
            'schemaCache'         => 'cache',
        ],
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
            // Sends all mails to a file/db by default.
            // You have to set 'useFileTransport' to `false` and
            // configure a transport for the mailer to send real emails.
            // eg.
            // 'transport' => [
            //     'class'      => 'Swift_SmtpTransport',
            //     'host'       => 'test.myhost.net',
            //     'username'   => 'no-reply@myhost.net',
            //     'password'   => '123456',
            //     'port'       => '465',
            //     'encryption' => 'tls',
            // ],
        ],

        // Uncomment to activate Webhooks
        // ---
        // 'webhooks' => [
        //     'class' => 'presentator\api\base\Webhooks',
        //     'definitions' => [
        //         'presentator\api\models\ScreenComment' => [
        //             'create' => 'YOU_WEBHOOK_URL',        // notified after model create
        //             'update' => 'YOU_WEBHOOK_URL',        // notified after model update
        //             'delete' => 'YOU_WEBHOOK_URL',        // notified before model delete
        //             'expand' => ['fromUser', 'metaData'], // additional fields defined in the model's `extraFields()` method
        //             // ... for all definition options check the public properties of `presentator\api\behaviors\WebhooksBehavior`
        //         ],
        //         // ... any other model that extends `presentator\api\models\ActiveRecord`
        //     ],
        // ],

        // Uncomment to activate Firebase Cloud Firestore comments notifications
        // ---
        // 'firestore' => [
        //     'class'      => 'presentator\api\base\Firestore',
        //     'authConfig' => '/path/to/firebase-auth.json',
        //     'projectId'  => 'presentator-v2',
        // ],

        // Uncomment to activate OAuth authentication
        // ---
        // 'authClientCollection' => [
        //     'class' => 'yii\authclient\Collection',
        //     'clients' => [
        //         'google' => [
        //             'class'        => 'yii\authclient\clients\Google',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //         'facebook' => [
        //             'class'        => 'yii\authclient\clients\Facebook',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //         'github' => [
        //             'class'        => 'yii\authclient\clients\GitHub',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //         'gitlab' => [
        //             'class'        => 'presentator\api\authclients\GitLab',
        //             'serviceUrl'   => 'https://gitlab.com',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //         'gitea' => [
        //             'class'        => 'presentator\api\authclients\Gitea',
        //             'serviceUrl'   => 'https://my-gitea.com',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //         'nextcloud' => [
        //             'class'        => 'presentator\api\authclients\Nextcloud',
        //             'serviceUrl'   => 'https://my-nextcloud.com',
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //     ],
        // ],
    ],
];
