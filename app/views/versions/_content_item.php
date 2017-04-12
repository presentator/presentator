<?php
/**
 * $model            \common\models\Version
 * $isActive         boolean
 * $commentCounters  array
 * $lazyLoadPriority string
 */

// Default values
$isActive         = isset($isActive) ? $isActive : false;
$commentCounters  = isset($commentCounters) ? $commentCounters : [];
$lazyLoadPriority = isset($lazyLoadPriority) ? $lazyLoadPriority : 'medium';
?>

<div id="version_screens_<?= $model->id ?>"
    class="tab-item version-screens screens-list <?= $isActive ? 'active' : '' ?>"
    data-version-id="<?= $model->id ?>"
>
    <div data-popup="#screens_upload_popup" class="box action-box primary disable-sort">
        <div class="content">
            <div class="table-wrapper">
                <div class="table-cell">
                    <span class="icon"><i class="ion ion-ios-plus-outline"></i></span>
                    <span class="txt"><?= Yii::t('app', 'Add screens') ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($model->screens as $screen): ?>
        <?= $this->render('/screens/_list_item', [
            'model'            => $screen,
            'newComments'      => (isset($commentCounters[$screen->id]) ? $commentCounters[$screen->id] : 0),
            'lazyLoadPriority' => $lazyLoadPriority,
        ]) ?>
    <?php endforeach ?>
</div>
