<?php
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/base.php'),
    (file_exists(__DIR__ . '/base-local.php') ? require(__DIR__ . '/base-local.php') : []),
    require(__DIR__ . '/api.php'),
    (file_exists(__DIR__ . '/api-local.php') ? require(__DIR__ . '/api-local.php') : []),
    require(__DIR__ . '/test.php'),
    (file_exists(__DIR__ . '/test-local.php') ? require(__DIR__ . '/test-local.php') : []),
    [
        // add custom app configuration for the codeception Yii2 module
    ]
);
