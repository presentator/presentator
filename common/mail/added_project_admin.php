<?php
use yii\helpers\Html;

/**
 * @var $user    \common\models\User
 * @var $project \common\models\Project
 */

$name       = $user->getFullName();
$projectUrl = Yii::$app->mainUrlManager->createUrl(['projects/view', 'id' => $project->id], true);
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?=
        Yii::t('mail', 'You are assigned as admin to project "{projectTitle}".', [
            'projectTitle' => Html::encode($project->title),
        ]);
    ?> <?=
        Yii::t('mail', 'Click on the button below to explore the project:');
    ?>
</p>

<p style="text-align: center;">
    <a href="<?= Html::encode($projectUrl) ?>" class="btn"><?= Yii::t('mail', 'Explore project') ?></a><br/>
    <a href="<?= Html::encode($projectUrl) ?>" class="hint"><?= Html::encode($projectUrl) ?></a>
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
