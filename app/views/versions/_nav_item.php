<?php
/**
 * $model    \common\models\Version
 * $isActive boolean
 */

$isActive = isset($isActive) ? $isActive : false;
?>
<li class="nav-item version-item <?= $isActive ? 'active' : '' ?>" data-version-id="<?= $model->id ?>">
    <span class="txt"><?= Yii::t('app', 'Version') ?></span>
    <span class="delete-handle version-delete"><i class="ion ion-close-circled"></i></span>
</li>
