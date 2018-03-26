<?php
use yii\web\View;
use yii\helpers\Url;
use common\components\helpers\CArrayHelper;
use common\widgets\CActiveForm;

/**
 * @var $this              \yii\web\View
 * @var $user              \common\models\User
 * @var $profileForm       \app\models\UserProfileForm
 * @var $passwordForm      \app\models\UserPasswordForm
 * @var $notificationsForm \app\models\UserNotificationsForm
 */

$hasAvatar = !empty($user->getAvatarUrl());

$this->title = Yii::t('app', 'Settings');
?>

<div class="base-wrapper">
    <?= $this->render('_avatar', [
        'model' => $user,
    ]) ?>

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

<?php
$this->registerJsFile('/js/hotspots.js?v=1521905187');
$this->registerJsFile('/js/avatar.view.js?v=1521905187');
$this->registerJsFile('/js/profile.view.js?v=1521905187');
$this->registerJs('
    var avatarView = new AvatarView({
        maxUploadSize:           ' . CArrayHelper::getValue(Yii::$app->params, 'maxUploadSize', 15) . ',
        ajaxTempAvatarUploadUrl: "' . Url::to(['users/ajax-temp-avatar-upload', 'id' => $user->id]) . '",
        ajaxSaveAvatarUrl:       "' . Url::to(['users/ajax-avatar-save', 'id' => $user->id]) . '",
        ajaxDeleteAvatarUrl:     "' . Url::to(['users/ajax-avatar-delete', 'id' => $user->id]) . '"
    });

    var profileView = new ProfileView({
        emailChangeTokenExpire: ' . CArrayHelper::getValue(Yii::$app->params, 'emailChangeTokenExpire', 1800) . ',

        // ajax urls
        ajaxNotificationsSaveUrl: "' . Url::to(['users/ajax-notifications-save']) . '",
        ajaxPasswordSaveUrl:      "' . Url::to(['users/ajax-password-save']) . '",
        ajaxProfielSaveUrl:       "' . Url::to(['users/ajax-profile-save']) . '",

        // texts
        pendingEmailHintText: "' . Yii::t('app', 'Confirmation email was sent to {pendingEmail}.') . '"
    });
', View::POS_READY, 'settings-js');
