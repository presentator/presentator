<?php
return [
    'id' => 'presentator-console',
    'controllerNamespace' => 'presentator\api\commands',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationTable' => '{{%Migration}}',
        ],
    ],
];
