<?php
$dbHost = getenv('DB_HOST') or 'localhost';
$dbName = getenv('DB_NAME') or 'presentator';
$dbUser = getenv('DB_USER') or 'root';
$dbPass = getenv('DB_PASSWORD') or '';

return [
    'components' => [
        'db' => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=' . $dbHost . ';dbname=' . $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'charset'  => 'utf8',
        ],
        'mailer' => [
            'class' => 'common\components\swiftmailer\CMailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
            // send all mails to a file/db by default. You have to set
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
