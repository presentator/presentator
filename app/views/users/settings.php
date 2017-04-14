<?php
use yii\web\View;
use yii\helpers\Url;
use common\widgets\CActiveForm;

/**
 * @var $this       \yii\web\View
 * @var $user       \common\models\User
 * @var $userForm   \app\models\SettingsForm
 * @var $avatarForm \app\models\AvatarForm
 */

$hasAvatar = !empty($user->getAvatarUrl());
?>

<?php $this->beginBlock('page_title'); ?>
<h3 class="page-title">
    <div class="item"><?= Yii::t('app', 'Settings') ?></div>
</h3>
<?php $this->endBlock(); ?>

<div class="base-wrapper">
    <div class="table-wrapper">
        <div class="table-cell min-width">
            <figure class="avatar large">
            <img data-src="<?= $user->getAvatarUrl(true) ?>?v=<?= time() ?>" alt="Avatar" class="lazy-load avatar-img">
            </figure>
        </div>
        <div class="table-cell min-width p-l-10">
            <button type="button" data-popup="#avatar_popup" class="btn btn-primary btn-ghost btn-sm m-l-5"><?= Yii::t('app', 'Change avatar') ?></button>
        </div>
        <div class="table-cell max-width p-l-10">
            <a href="#" class="danger-link hint m-l-5 delete-avatar" data-action-confirm="<?= Yii::t('app', 'Do you really want to delete the user avatar?') ?>" <?= !$hasAvatar ? 'style="display: none"' : ''; ?>>
                <i class="ion ion-trash-a"></i>
                <span class="txt"><?= Yii::t('app', 'Delete avatar') ?></span>
            </a>
        </div>
    </div>

    <div class="clearfix m-b-30"></div>

    <div class="panel padded">
        <?php $form = CActiveForm::begin(); ?>
            <h6 class="faded"><?= Yii::t('app', 'General') ?></h6>
            <div class="row">
                <div class="cols-4">
                    <?= $form->field($userForm, 'email', ['inputOptions' => ['disabled' => 'disabled']]) ?>
                </div>
                <div class="cols-4">
                    <?= $form->field($userForm, 'firstName') ?>
                </div>
                <div class="cols-4">
                    <?= $form->field($userForm, 'lastName') ?>
                </div>
            </div>

            <div class="row">
                <div class="cols-12">
                    <?= $form->field($userForm, 'notifications')->checkbox() ?>
                </div>
            </div>

            <h6 class="faded"><?= Yii::t('app', 'Security') ?></h6>
            <?= $form->field($userForm, 'changePassword')->checkbox(['data-toggle' => '#change_password_block']) ?>
            <div class="row" id="change_password_block">
                <div class="cols-4">
                    <?= $form->field($userForm, 'oldPassword')->passwordInput() ?>
                </div>
                <div class="cols-4">
                    <?= $form->field($userForm, 'newPassword')->passwordInput() ?>
                </div>
                <div class="cols-4">
                    <?= $form->field($userForm, 'newPasswordConfirm')->passwordInput() ?>
                </div>
            </div>

            <hr class="m-t-0">
            <div class="block text-center">
                <button class="btn btn-primary btn-cons"><?= Yii::t('app', 'Save changes') ?></button>
            </div>
        <?php CActiveForm::end(); ?>
    </div>
</div>

<div id="avatar_popup" class="popup small" data-overlay-close="false">
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
                    <img data-src="<?= $user->getAvatarUrl(false) ?>?v=<?= time() ?>" data-preview-url="<?= $user->getAvatarUrl(false) ?>?v=<?= time() ?>" id="preview_img" class="lazy-load preview-image" alt="Preview image"/>

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

<?php
$this->registerJsFile('/js/hotspots.js');
$this->registerJsFile('/js/profile.view.js');
$this->registerJs('
    var profileView = new ProfileView({
        tempAvatarUploadUrl: "' . Url::to(['users/ajax-temp-avatar-upload']) . '",
        saveAvatarUrl:       "' . Url::to(['users/ajax-avatar-save']) . '",
        deleteAvatarUrl:     "' . Url::to(['users/ajax-avatar-delete']) . '"
    });
', View::POS_READY, 'settings-js');
