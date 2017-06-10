<?php
use yii\helpers\Html;

/**
 * @var $user    \common\models\User
 * @var $project \common\models\Project
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?= Yii::t('mail', 'You are no longer an administrator to project "{projectTitle}".', ['projectTitle' => Html::encode($project->title)]) ?>
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
