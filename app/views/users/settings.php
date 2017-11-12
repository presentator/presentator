<?php
use yii\web\View;
use yii\helpers\Url;
use common\widgets\CActiveForm;

/**
 * @var $this              \yii\web\View
 * @var $user              \common\models\User
 * @var $avatarForm        \app\models\AvatarForm
 * @var $profileForm       \app\models\UserProfileForm
 * @var $passwordForm      \app\models\UserPasswordForm
 * @var $notificationsForm \app\models\UserNotificationsForm
 */

$hasAvatar = !empty($user->getAvatarUrl());

$this->title = Yii::t('app', 'Settings');
?>

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
            <a href="#" class="danger-link hint-link m-l-5 delete-avatar" data-action-confirm="<?= Yii::t('app', 'Do you really want to delete the user avatar?') ?>" <?= !$hasAvatar ? 'style="display: none"' : ''; ?>>
                <i class="ion ion-trash-a"></i>
                <span class="txt"><?= Yii::t('app', 'Delete avatar') ?></span>
            </a>
        </div>
    </div>

    <div class="clearfix m-b-30"></div>

    <div class="panel">
        <div id="user_tabs" class="block tabs p-t-25">
            <div class="tabs-header">
                <div class="tab-item active" data-target="#user_profile"><span class="txt"><?= Yii::t('app', 'Profile') ?></span></div>
                <div class="tab-item" data-target="#user_password"><span class="txt"><?= Yii::t('app', 'Password') ?></span></div>
                <div class="tab-item" data-target="#user_notifications"><span class="txt"><?= Yii::t('app', 'Notifications') ?></span></div>
            </div>

            <div class="tabs-content">
                <div id="user_profile" class="tab-item active">
                    <?= $this->render('_profile_form', ['model' => $profileForm]) ?>
                </div>

                <div id="user_password" class="tab-item">
                    <?= $this->render('_password_form', ['model' => $passwordForm]) ?>
                </div>

                <div id="user_notifications" class="tab-item">
                    <?= $this->render('_notifications_form', ['model' => $notificationsForm]) ?>
                </div>
            </div>
        </div>
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
                    <img data-src="<?= $user->getAvatarUrl(false) ?>" data-preview-url="<?= $user->getAvatarUrl(false) ?>" data-nocache="1" id="preview_img" class="lazy-load preview-image" alt="Preview image"/>

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
$this->registerJsFile('/js/hotspots.js?v=1507457981');
$this->registerJsFile('/js/profile.view.js?v=1507457981');
$this->registerJs('
    var profileView = new ProfileView({
        ajaxNotificationsSaveUrl: "' . Url::to(['users/ajax-notifications-save']) . '",
        ajaxPasswordSaveUrl:      "' . Url::to(['users/ajax-password-save']) . '",
        ajaxProfielSaveUrl:       "' . Url::to(['users/ajax-profile-save']) . '",
        ajaxTempAvatarUploadUrl:  "' . Url::to(['users/ajax-temp-avatar-upload']) . '",
        ajaxSaveAvatarUrl:        "' . Url::to(['users/ajax-avatar-save']) . '",
        ajaxDeleteAvatarUrl:      "' . Url::to(['users/ajax-avatar-delete']) . '"
    });
', View::POS_READY, 'settings-js');
