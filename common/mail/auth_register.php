<?php
use yii\helpers\Html;

/**
 * @var $user     \common\models\User
 * @var $password string
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?= Yii::t('mail', 'Thank you for registering to {appLink}.', [
        'appLink' => Html::a('presentator.io', Yii::$app->mainUrlManager->createUrl(['site/index'], true)),
    ]) ?>
</p>
<p><?= Yii::t('mail', 'We have generated a random password for your account in order to be able to login without the need of 3rd party authentication.') ?></p>
<p><strong>NB!</strong>&nbsp;<?= Yii::t('mail', 'For security reasons, we encourage you to change the generated password as soon as possible.') ?></p>
<p style="text-align: center;" class="emphasis">
    <strong><?= $password ?></strong>
</p>
<p>
    <?= Yii::t('mail', "If you need any further help don't hesitate to contact us at {supportEmail}.", [
        'supportEmail' => Html::mailto(Yii::$app->params['supportEmail']),
    ]) ?>
</p>
<p>
    <?= Yii::t('mail', 'Best Regards') ?>, <br/>
    <?= Yii::t('mail', 'Presentator Team') ?>
</p>
