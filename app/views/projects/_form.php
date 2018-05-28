<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\Project;
use common\widgets\CActiveForm;

/**
 * @var $model       \app\models\ProjectForm
 * @var $isNewRecord boolean
 */

$isUpdate = $model->isUpdate();
if ($isUpdate) {
    $formId = 'project_update_form';
} else {
    $formId = 'project_create_form';
}

$project = $model->getProject();
?>
<?php $form = CActiveForm::begin([
    'id'      => $formId,
    'options' => ['autocomplete' => 'off'],
]); ?>
    <?= $form->field($model, 'title') ?>

    <?=
        $form->field($model, 'isPasswordProtected', ['options' => ['class' => 'form-group m-b-20']])
            ->checkbox(['data-toggle' => '#password_block'])
    ?>
    <div class="block" id="password_block">
        <?php
            if ($model->isPasswordProtected) {
                echo $form->field($model, 'changePassword', ['options' => ['class' => 'form-group m-b-20']])
                    ->checkbox(['data-toggle' => '#password_field_wrapper']);
            }
        ?>
        <div class="block" id="password_field_wrapper">
            <?=
                $form->field($model, 'password', ['options' => ['class' => 'form-group m-b-20']])
                    ->passwordInput(['placeholder' => Yii::t('app', 'Type your password here...')])
                    ->label(false)
            ?>
        </div>
    </div>

    <div class="block text-center">
        <button class="btn btn-primary btn-cons m-t-10 btn-loader">
            <?php if ($isUpdate): ?>
                <?= Yii::t('app', 'Save changes') ?>
            <?php else: ?>
                <?= Yii::t('app', 'Create project') ?>
            <?php endif ?>
        </button>

        <?php if ($project): ?>
            <div class="clearfix m-t-10"></div>

            <a href="<?= Url::to(['projects/delete', 'id' => $project->id]) ?>"
                class="danger-link hint-link project-delete-link"
                data-method="post"
                data-confirm="<?= Yii::t('app', 'Do you really want to delete project {projectTitle}?', ['projectTitle' => Html::encode($project->title)]) ?>"
            >
                <?= Yii::t('app', 'Delete project') ?>
            </a>
        <?php endif; ?>
    </div>
<?php CActiveForm::end(); ?>
