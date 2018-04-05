<?php
use yii\helpers\Html;
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\UserProfileForm
 */
?>

<?php $form = CActiveForm::begin([
    'id' => 'user_profile_form',
]); ?>
    <div class="row">
        <div class="cols-6">
            <?= $form->field($model, 'firstName') ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'lastName') ?>
        </div>
    </div>

    <div class="row">
        <div class="cols-6">
            <?= $form->field($model, 'email', ['inputOptions' => [
                'data-original-email' => $model->email,
            ]]) ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'password', ['options' => ['class' => 'form-group hidden']])->passwordInput() ?>
        </div>
    </div>

    <hr class="m-t-0">

    <div class="block">
        <button type="button" class="btn btn-ghost left reset-form-handle"><?= Yii::t('app', 'Cancel') ?></button>
        <button class="btn btn-primary btn-cons right"><?= Yii::t('app', 'Save changes') ?></button>
    </div>
<?php CActiveForm::end(); ?>
