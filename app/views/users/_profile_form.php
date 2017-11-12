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

    <div class="table-wrapper">
        <div class="table-cell text-left">
            <span class="default-link reset-form-handle">Cancel changes</span>
        </div>
        <div class="table-cell text-right">
            <button class="btn btn-primary"><?= Yii::t('app', 'Save changes') ?></button>
        </div>
    </div>
<?php CActiveForm::end(); ?>
