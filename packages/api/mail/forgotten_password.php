<?php
use yii\helpers\Html;

/**
 * @var $user \presentator\api\models\User
 */

$name     = $user->getFullName();
$resetUrl = str_replace('{token}', $user->passwordResetToken, Yii::$app->params['passwordResetUrl']);
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?=
        Yii::t('mail', "We've received a request to reset your account password.");
    ?> <?=
        Yii::t('mail', 'To update your password, click the button below:');
    ?>
</p>

<div style="text-align: center;">
    <a href="<?= Html::encode($resetUrl) ?>" class="btn"><?= Yii::t('mail', 'Reset password') ?></a><br/>
    <a href="<?= Html::encode($resetUrl) ?>" class="hint hidden"><?= Html::encode($resetUrl) ?></a>
</div>
