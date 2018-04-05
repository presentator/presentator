<?php

/**
 * @var $model \common\models\User
 */

$hasAvatar = !empty($model->getAvatarUrl());

?>
<div class="table-wrapper">
    <div class="table-cell min-width">
        <figure class="avatar large">
            <img data-src="<?= $model->getAvatarUrl(true) ?>?v=<?= time() ?>" alt="Avatar" class="lazy-load avatar-img">
        </figure>
    </div>
    <div class="table-cell min-width p-l-10">
        <button type="button" data-popup="#avatar_popup" class="btn btn-primary btn-ghost btn-sm m-l-5"><?= Yii::t('app', 'Change avatar') ?></button>
    </div>
    <div class="table-cell max-width p-l-10">
        <a href="#" class="danger-link hint-link m-l-5 delete-avatar" data-action-confirm="<?= Yii::t('app', 'Do you really want to delete the user avatar?') ?>" <?= !$hasAvatar ? 'style="display: none"' : ''; ?>>
            <i class="ion ion-trash-a"></i>
            <span class="txt"><?= Yii::t('app', 'Delete avatar') ?></span>
        </a>
    </div>
</div>

<div id="avatar_popup" class="popup popup-small" data-overlay-close="false">
    <div class="popup-content">
        <h3 class="popup-title text-center"><?= Yii::t('app', 'Avatar') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <div id="upload_container" class="upload-container" <?= $hasAvatar ? 'style="display: none;"' : '' ?>>
                <div class="loader-wrapper">
                    <div class="loader"></div>
                    <p><?= Yii::t('app', 'Uploading and generating thumbs...') ?></p>
                </div>

                <div class="content dz-message">
                    <i class="ion ion-android-upload"></i>
                    <p><?= Yii::t('app', 'Click or drop here to upload') ?> <em>(png, jpg)</em></p>
                </div>
            </div>

            <div id="preview_container" class="preview-container" <?= !$hasAvatar ? 'style="display: none;"' : '' ?>>
                <figure class="preview-image-wrapper">
                    <img data-src="<?= $model->getAvatarUrl(false) ?>" data-preview-url="<?= $model->getAvatarUrl(false) ?>" data-nocache="1" id="preview_img" class="lazy-load preview-image" alt="Preview image"/>

                    <div id="crop_hotspot" class="hotspot crop-hotspot"><span class="resize-handle"></span></div>
                </figure>
                <span id="preview_remove" class="danger-link preview-remove"><?= Yii::t('app', 'Upload another') ?></span>

                <div class="clearfix m-b-15"></div>
                <div class="block text-center">
                    <button type="button" id="persist_avatar" class="btn btn-sm btn-cons btn-primary"><?= Yii::t('app', 'Save') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
