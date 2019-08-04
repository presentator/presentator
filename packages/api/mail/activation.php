<?php
use yii\helpers\Html;

/**
 * @var $user  \presentator\api\models\User
 * @var $token null|string
 */

$name          = $user->getFullName();
$token         = $token ?? $user->generateActivationToken();
$activationUrl = str_replace('{token}', $token, Yii::$app->params['activationUrl']);
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?=
        Yii::t('mail', 'Thank you for your registration at Presentator.');
    ?> <?=
        Yii::t('mail', 'Click on the button below to activate your account:');
    ?>
</p>

<div style="text-align: center;">
    <a href="<?= Html::encode($activationUrl) ?>" class="btn"><?= Yii::t('mail', 'Activate account') ?></a><br/>
    <a href="<?= Html::encode($activationUrl) ?>" class="hint hidden"><?= Html::encode($activationUrl) ?></a>
</div>
