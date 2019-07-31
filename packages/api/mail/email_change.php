<?php
use yii\helpers\Html;

/**
 * @var $user     \app\models\User
 * @var $newEmail string
 * @var $token    null|string
 */

$name      = $user->getFullName();
$token     = $token ?? $user->generateEmailChangeToken($newEmail);
$changeUrl = str_replace('{token}', $token, Yii::$app->params['emailChangeUrl']);
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?= Yii::t('mail', "We've received a request to change your Presentator account email address from {oldEmail} to {newEmail}.", [
        'oldEmail' => Html::encode($user->email),
        'newEmail' => Html::encode($newEmail),
    ]) ?>
</p>

<div style="text-align: center;">
    <a href="<?= Html::encode($changeUrl) ?>" class="btn"><?= Yii::t('mail', 'Confirm email address change') ?></a><br/>
    <a href="<?= Html::encode($changeUrl) ?>" class="hint hidden"><?= Html::encode($changeUrl) ?></a>
</div>
