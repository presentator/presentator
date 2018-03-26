<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\widgets\CActiveForm;
use app\models\SuperUserForm;

/**
 * @var $model        \app\models\SuperUserForm
 * @var $statusesList array
 * @var $typesList    array
 */
?>

<?php $form = CActiveForm::begin(['id' => 'super_user_form']); ?>
    <?= $form->field($model, 'email') ?>

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
            <?= $form->field($model, 'status')->dropDownList($statusesList) ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'type')->dropDownList($typesList) ?>
        </div>
    </div>

    <?php if ($model->scenario == SuperUserForm::SCENARIO_UPDATE): ?>
        <?=
            $form->field($model, 'changePassword', ['options' => ['class' => 'form-group m-b-20']])
                ->checkbox(['data-toggle' => '#password_block'])
        ?>
    <?php endif; ?>

    <div class="row" id="password_block">
        <div class="cols-6">
            <?= $form->field($model, 'password')->passwordInput() ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'passwordConfirm')->passwordInput() ?>
        </div>
    </div>

    <hr class="m-t-0">

    <?= $form->field($model, 'notifications')->checkbox() ?>
    <?= $form->field($model, 'mentions')->checkbox() ?>

    <hr>

    <div class="block">
        <a href="<?= Url::to(['users/index']) ?>" class="btn btn-ghost left"><?= Yii::t('app', 'Cancel') ?></a>

        <?php if ($model->scenario == SuperUserForm::SCENARIO_UPDATE): ?>
            <button class="btn btn-cons btn-primary right"><?= Yii::t('app', 'Save changes') ?></button>
        <?php else: ?>
            <button class="btn btn-cons btn-primary right"><?= Yii::t('app', 'Create user') ?></button>
        <?php endif; ?>
    </div>
<?php CActiveForm::end(); ?>
