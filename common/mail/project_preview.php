<?php
use yii\helpers\Html;

/**
 * @var $preview common\models\ProjectPreview
 */

$previewUrl = Yii::$app->mainUrlManager->createUrl(['preview/view', 'slug' => $preview->slug], true);
?>

<p><?= Yii::t('mail', 'Hello') ?>,</p>
<p>
    <?= Yii::t('mail', 'You are invited to review the design for "{projectTitle}".', ['projectTitle' => Html::encode($preview->project->title)]) ?>
    <?= Yii::t('mail', 'Click on the button below to continue:') ?>
</p>
<p style="text-align: center;">
    <a href="<?= $previewUrl ?>" class="btn"><?= Yii::t('mail', 'View project') ?></a><br>
    <a href="<?= $previewUrl ?>" class="hint"><?= $previewUrl ?></a>
</p>
<p>
    <?= Yii::t('mail', "If you need any further help don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br>
    <?= Yii::t('mail', 'Presentator.io Team') ?>
</p>
