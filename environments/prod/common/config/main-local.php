<?php
return [
    'components' => [
        'db' => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=localhost;dbname=presentator',
            'username' => 'root',
            'password' => '',
            'charset'  => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            // eg.
            // 'transport' => [
            //     'class'      => 'Swift_SmtpTransport',
            //     'host'       => 'test.myhost.net',
            //     'username'   => 'no-reply@myhost.net',
            //     'password'   => '123456',
            //     'port'       => '465',
            //     'encryption' => 'ssl',
            // ],
        ],
    ],
];
