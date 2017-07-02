<?php
use yii\helpers\Html;

/**
 * $model    \common\models\Version
 * $isActive boolean
 */

$isActive = isset($isActive) ? $isActive : false;

if ($model->title) {
    $title = Html::encode($model->title);
} else {
    $title = Yii::t('app', 'Version');
}
?>
<li class="nav-item version-item <?= $model->title  ? 'has-title' : '' ?> <?= $isActive ? 'active' : '' ?>"
    data-version-id="<?= $model->id ?>"
    data-project-id="<?= $model->projectId ?>"
    <?php if ($model->title): ?>
    title="<?= $title ?>"
    <?php endif; ?>
>
    <span class="txt version-title-holder"><?= $title ?></span>
    <div class="ctrl-bar">
        <span class="ctrl-item version-edit"><i class="ion ion-edit"></i></span>
    </div>
</li>
