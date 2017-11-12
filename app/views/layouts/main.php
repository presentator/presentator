<?php
use yii\helpers\Html;
use app\assets\AppAsset;
use common\widgets\FlashAlert;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

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
    <title><?= $this->title ? (Html::encode($this->title) . ' - ') : '' ?>Presentator</title>
    <link href="/images/favicon.png?v=1499601723" rel="shortcut icon" type="image/png">
    <?php $this->head() ?>
</head>
<body class="<?= isset($this->params['bodyClass']) ? $this->params['bodyClass'] : ''; ?> lang-<?= substr(Yii::$app->language, 0, 2) ?>">
    <?php $this->beginBody() ?>
        <?= $this->render('_sidebar'); ?>

        <?php if (isset($this->blocks['before_global_wrapper'])): ?>
            <?= $this->blocks['before_global_wrapper'] ?>
        <?php endif; ?>

        <div id="global_wrapper" class="global-wrapper">
            <?= $this->render('_header'); ?>

            <main id="page_content" class="page-content">
                <?= FlashAlert::widget(['options' => ['class' => 'bottom-margin', 'data-auto-hide' => 4000]]) ?>

                <?= $content ?>
            </main>

            <?= $this->render('_footer'); ?>
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
