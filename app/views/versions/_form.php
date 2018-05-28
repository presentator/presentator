<?php
use yii\helpers\Html;
use common\models\Version;
use common\widgets\CActiveForm;

/**
 * @var $model        \app\models\VersionForm
 * @var $typesList    array
 * @var $subtypesList array
 */

$isUpdate = $model->isUpdate();

if ($isUpdate) {
    $versionId = $model->version->id;
    $formId    = 'version_update_form';
} else {
    $versionId = null;
    $formId    = 'version_create_form';
}
?>

<?php $form = CActiveForm::begin(['id' => $formId]); ?>
    <?= Html::hiddenInput('versionId', $versionId); ?>

    <?= $form->field($model, 'title', [
        'inputOptions' => [
            'placeholder' => Yii::t('app', 'Version title (optional)')
        ]
    ])->label(false) ?>

    <?= $this->render('_form_type_selection', [
        'form'  => $form,
        'model' => $model,
    ]) ?>

    <div class="block text-center">
        <button class="btn btn-primary btn-cons btn-loader">
            <?php if (!$isUpdate): ?>
                <?= Yii::t('app', 'Create new version') ?>
            <?php else: ?>
                <?= Yii::t('app', 'Save changes') ?>
            <?php endif ?>
        </button>

        <?php if ($isUpdate): ?>
            <div class="clearfix m-t-10"></div>
            <span class="danger-link hint-link version-delete"
                data-version-id="<?= $versionId ?>"
                data-confirm-text="<?= Yii::t('app', 'Do you really want to deleted the selected version and all of its screens?') ?>"
            >
                <?= Yii::t('app', 'Delete version') ?>
            </span>
        <?php endif ?>
    </div>
<?php CActiveForm::end(); ?>
