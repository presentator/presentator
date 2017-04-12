<?php

/**
 * @var $this \yii\web\View
 * @var $content string
 */

use app\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= $this->title ? (Html::encode($this->title) . ' - ') : '' ?>Presentator.io</title>
    <?php $this->head() ?>
</head>
<body class="<?= isset($this->params['bodyClass']) ? $this->params['bodyClass'] : ''; ?> lang-<?= substr(Yii::$app->language, 0, 2) ?>">
    <?php $this->beginBody() ?>
        <?php if (isset($this->blocks['before_global_wrapper'])): ?>
            <?= $this->blocks['before_global_wrapper'] ?>
        <?php endif; ?>

        <div id="global_wrapper" class="global-wrapper <?= isset($this->params['globalWrapperClass']) ? $this->params['globalWrapperClass'] : ''; ?>">
            <?= $content ?>
        </div>

        <?php if (isset($this->blocks['after_global_wrapper'])): ?>
            <?= $this->blocks['after_global_wrapper'] ?>
        <?php endif; ?>

        <div id="notifications_wrapper" class="notifications-wrapper"></div>
        <div id="global_loader" class="global-loader"><i class="ion ion-load-c"></i></div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
