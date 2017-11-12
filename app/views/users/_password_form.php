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

    <div class="table-wrapper">
        <div class="table-cell text-left">
            <span class="default-link reset-form-handle">Cancel changes</span>
        </div>
        <div class="table-cell text-right">
            <button class="btn btn-primary"><?= Yii::t('app', 'Save changes') ?></button>
        </div>
    </div>
<?php CActiveForm::end(); ?>
