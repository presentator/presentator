<?php
use yii\helpers\Html;

/**
 * @var $user    \app\models\User
 * @var $project \app\models\Project
 */

$name       = $user->getFullName();
$exploreUrl = Yii::$app->params['appUrl'];
?>
<p><?= Yii::t('mail', 'Hello') ?><?= $name ? (' ' . Html::encode($name)) : '' ?>,</p>

<p>
    <?=
        Yii::t('mail', 'You have been assigned as administrator to project "{projectTitle}".', [
            'projectTitle' => Html::encode($project->title),
        ]);
    ?> <?=
        Yii::t('mail', 'Click on the button below to explore the project:');
    ?>

    <p style="text-align: center;">
        <a href="<?= Html::encode($exploreUrl) ?>" class="btn"><?= Yii::t('mail', 'Explore project') ?></a><br/>
        <a href="<?= Html::encode($exploreUrl) ?>" class="hint hidden"><?= Html::encode($exploreUrl) ?></a>
    </p>
</p>
