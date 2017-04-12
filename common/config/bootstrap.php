<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('@main', dirname(dirname(__DIR__)) . '/app'); // @app is reserved
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@mainWeb', dirname(dirname(__DIR__)) . '/app/web');
