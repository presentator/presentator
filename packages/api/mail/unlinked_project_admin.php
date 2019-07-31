<?php
use yii\helpers\Html;

/**
 * @var $user    \app\models\User
 * @var $project \app\models\Project
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?= Yii::t('mail', 'You have been discharged as administrator from project "{projectTitle}".', [
        'projectTitle' => Html::encode($project->title),
    ]) ?>
</p>
