<?php
use yii\helpers\Html;

/**
 * @var $user     common\models\User
 * @var $newEmail string
 */

$name = $user->getFullName();
$changeUrl = Yii::$app->mainUrlManager->createUrl([
    'site/change-email',
    'token' => $user->emailChangeToken,
    'email' => $newEmail,
], true);
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?= Yii::t('mail', "We've received a request to change your Presentator account email address from {oldEmail} to {newEmail}.", [
        'oldEmail' => $user->email,
        'newEmail' => $newEmail,
    ]) ?>
    <?= Yii::t('mail', 'Click on the button below to update it:') ?>
</p>
<p style="text-align: center;">
    <a href="<?= $changeUrl ?>" class="btn"><?= Yii::t('mail', 'Change email') ?></a>
</p>
<p>
    <?= Yii::t('mail', "If you think that this message is a mistake or you need any further help, don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br/>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>
