<?php
define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require __DIR__ .'/../vendor/autoload.php';

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug'        => true,
    'cacheDir'     => '/tmp/presentator',
    'includePaths' => [__DIR__. '/..'],
    'excludePaths' => [
        (__DIR__ . '/../vendor/goaop'),
        (__DIR__ . '/../vendor/nikic'),
        (__DIR__ . '/../vendor/bin'),
    ],
]);
$kernel->loadFile(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
