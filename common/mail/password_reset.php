<?php
use yii\helpers\Html;

/**
 * @var $user common\models\User
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?= Yii::t('mail', "We've received a request to reset your account password.") ?>
    <?= Yii::t('mail', 'Click on the button below to reset it and set a new one:') ?>
</p>
<p style="text-align: center;">
    <a href="<?= Yii::$app->mainUrlManager->createUrl(['site/reset-password', 'token' => $user->passwordResetToken], true) ?>" class="btn"><?= Yii::t('mail', 'Reset password') ?></a>
</p>
<p>
    <?= Yii::t('mail', "If you think that this message is a mistake or you need any further help, don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>
