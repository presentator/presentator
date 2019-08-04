<?php
use yii\helpers\Html;

/**
 * @var $user    \presentator\api\models\User
 * @var $project \presentator\api\models\Project
 */

$name = $user->getFullName();
?>

<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?= Yii::t('mail', 'You have been removed from project "{projectTitle}".', [
        'projectTitle' => Html::encode($project->title),
    ]) ?>
</p>
