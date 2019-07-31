<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../config/base.php'),
    require(__DIR__ . '/../config/base-local.php'),
    require(__DIR__ . '/../config/api.php'),
    require(__DIR__ . '/../config/api-local.php')
);

(new yii\web\Application($config))->run();
