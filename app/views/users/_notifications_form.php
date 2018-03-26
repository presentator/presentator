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

    <div class="block">
        <button type="button" class="btn btn-ghost left reset-form-handle"><?= Yii::t('app', 'Cancel') ?></button>
        <button class="btn btn-primary btn-cons right"><?= Yii::t('app', 'Save changes') ?></button>
    </div>
<?php CActiveForm::end(); ?>
