<?php
use yii\helpers\Html;

/**
 * @var $user     \presentator\api\models\User
 * @var $password string
 */

$name = $user->getFullName();
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?=
        Yii::t('mail', 'Your Presentator account was successfully created.');
    ?> <?=
        Yii::t('mail', 'We have generated a random password for your account in order to be able to login without the need of 3rd party authentication.');
    ?>
</p>

<p><strong>NB!</strong>&nbsp;<?= Yii::t('mail', 'For security reasons, we encourage you to change the generated password as soon as possible.') ?></p>

<p class="emphasis" style="text-align: center;"><strong><?= Html::encode($password) ?></strong></p>
