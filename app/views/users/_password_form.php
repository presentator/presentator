<?php
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\UserPasswordForm
 */
?>

<?php $form = CActiveForm::begin([
    'id' => 'user_password_form',
]); ?>
    <div class="row">
        <div class="cols-6">
            <?= $form->field($model, 'oldPassword')->passwordInput() ?>
        </div>
        <div class="cols-6"></div>
    </div>

    <div class="row">
        <div class="cols-6">
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'newPasswordConfirm')->passwordInput() ?>
        </div>
    </div>

    <hr class="m-t-0">

    <div class="block">
        <button type="button" class="btn btn-ghost left reset-form-handle"><?= Yii::t('app', 'Cancel') ?></button>
        <button class="btn btn-primary btn-cons right"><?= Yii::t('app', 'Save changes') ?></button>
    </div>
<?php CActiveForm::end(); ?>
