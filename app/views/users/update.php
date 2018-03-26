<?php
use yii\web\View;
use yii\helpers\Url;
use common\components\helpers\CArrayHelper;

/**
 * @var $this         \yii\web\View
 * @var $form         \app\models\SuperUserForm
 * @var $user         \common\models\User
 * @var $statusesList array
 * @var $typesList    array
 */

$this->title = Yii::t('app', 'Update User');
?>

<?php $this->beginBlock('page_title'); ?>
    <h3 class="page-title">
        <a href="<?= Url::to(['users/index']) ?>" class="item"><?= Yii::t('app', 'Users') ?></a>
        <span class="item project-title"><?= Yii::t('app', 'Update') ?></span>
    </h3>
<?php $this->endBlock(); ?>


<div class="base-wrapper">
    <?= $this->render('_avatar', [
        'model' => $user,
    ]) ?>

    <div class="clearfix m-b-30"></div>

    <div class="panel padded">
        <?= $this->render('_super_form', [
            'model'        => $form,
            'statusesList' => $statusesList,
            'typesList'    => $typesList,
        ]) ?>
    </div>
</div>

<?php
$this->registerJsFile('/js/hotspots.js?v=1521905187');
$this->registerJsFile('/js/avatar.view.js?v=1521905187');
$this->registerJs('
    var avatarView = new AvatarView({
        maxUploadSize:           ' . CArrayHelper::getValue(Yii::$app->params, 'maxUploadSize', 15) . ',
        ajaxTempAvatarUploadUrl: "' . Url::to(['users/ajax-temp-avatar-upload', 'id' => $user->id]) . '",
        ajaxSaveAvatarUrl:       "' . Url::to(['users/ajax-avatar-save', 'id' => $user->id]) . '",
        ajaxDeleteAvatarUrl:     "' . Url::to(['users/ajax-avatar-delete', 'id' => $user->id]) . '"
    });
', View::POS_READY, 'settings-js');
