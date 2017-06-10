<?php
use yii\helpers\Html;

/**
 * @var $user    \common\models\User
 * @var $project \common\models\Project
 */

$projectUrl = Yii::$app->mainUrlManager->createUrl(['projects/view', 'id' => $project->id], true);
$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>
<p>
    <?= Yii::t('mail', 'You are assigned as admin to project "{projectTitle}".', [
        'projectTitle' => Html::encode($project->title),
    ]) ?>
    <?= Yii::t('mail', 'Click on the button below to explore the project:') ?>
</p>
<p style="text-align: center;">
    <a href="<?= $projectUrl ?>" class="btn"><?= Yii::t('mail', 'Explore project') ?></a><br>
    <a href="<?= $projectUrl ?>" class="hint"><?= $projectUrl ?></a>
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
