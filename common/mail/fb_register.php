<?php
use yii\helpers\Html;

/**
 * @var $user     \common\models\User
 * @var $password string
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p><?= Yii::t('mail', 'You have successfully registered via Facebook.') ?></p>
<p><?= Yii::t('mail', 'To be able to login without the need of Facebook authentication, we have set a random password for your account:') ?></p>
<p style="text-align: center;" class="emphasis">
    <strong><?= $password ?></strong>
</p>
<p>
    <?= Yii::t('mail', "If you need any further help don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>
