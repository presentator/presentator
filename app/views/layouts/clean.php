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
<html lang="<?= Yii::$app->language ?>" class="preload">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= $this->title ? (Html::encode($this->title) . ' - ') : '' ?>Presentator</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#152598">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Presentator">
    <meta name="application-name" content="Presentator">
    <meta name="msapplication-TileColor" content="#152598">
    <meta name="msapplication-config" content="/images/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
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
        <div id="global_loader" class="global-loader"><i class="ion ion-ios-radio"></i></div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
