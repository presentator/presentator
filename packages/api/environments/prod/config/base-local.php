<?php
return [
    'components' => [
        'db' => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=localhost;dbname=presentator',
            'username' => 'presentator',
            'password' => '',
            'charset'  => 'utf8',
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
        //         'twitter' => [
        //             'class'        => 'yii\authclient\clients\TwitterOAuth2',
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
        //             'clientId'     => '',
        //             'clientSecret' => '',
        //         ],
        //     ],
        // ],
    ],
];
