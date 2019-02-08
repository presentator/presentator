<?php
use yii\helpers\Html;

/**
 * @var $user  \common\models\User
 * @var $token string
 */

$activationUrl = Yii::$app->mainUrlManager->createUrl(['site/activation', 'email' => $user->email, 'token' => $token], true);
?>

<p><?= Yii::t('mail', 'Hello') ?>,</p>
<p>
    <?=
        Yii::t('mail', 'Thank you for registering to {appLink}.', [
            'appLink' => Html::a('presentator.io', Yii::$app->mainUrlManager->createUrl(['site/index'], true)),
        ]);
    ?> <?=
        Yii::t('mail', 'Click on the button below to activate your account:');
    ?>
</p>

<p style="text-align: center;">
    <a href="<?= Html::encode($activationUrl) ?>" class="btn"><?= Yii::t('mail', 'Activate account') ?></a><br/>
    <a href="<?= Html::encode($activationUrl) ?>" class="hint"><?= Html::encode($activationUrl) ?></a>
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
