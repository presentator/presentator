<?php
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\UserNotificationsForm
 */
?>

<?php $form = CActiveForm::begin([
    'id' => 'user_notifications_form',
]); ?>
    <?= $form->field($model, 'notifications')->checkbox() ?>

    <?= $form->field($model, 'mentions')->checkbox() ?>

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
