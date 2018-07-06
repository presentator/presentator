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
>
    <div class="version-title-holder"
        <?php if ($model->title): ?>
        data-version-title="<?= $title ?>"
        title="<?= $title ?>"
        <?php endif; ?>
    >
        <?= $title ?>
    </div>

    <div class="ctrl-bar">
        <span class="ctrl-item version-edit" data-cursor-tooltip="<?= Yii::t('app', 'Edit version') ?>">
            <i class="ion ion-md-create"></i>
        </span>
    </div>
</li>
