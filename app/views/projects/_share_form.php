<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\widgets\CActiveForm;
use common\models\ProjectPreview;

/**
 * @var $model \app\models\ProjectShareForm
 */

$previewLinkAttributes = [
    'id'     => 'project_preview_link',
    'class'  => 'preview-link',
    'target' => '_blank',
];

if ($model->project && $model->project->previews) {
    $viewUrl           = $model->project->getPreviewUrl(ProjectPreview::TYPE_VIEW);
    $viewAndCommentUrl = $model->project->getPreviewUrl(ProjectPreview::TYPE_VIEW_AND_COMMENT);
}
?>
<?php $form = CActiveForm::begin(['id' => 'project_preview_share_form']); ?>
    <?= $form->field($model, 'email')->input('text', ['placeholder' => Yii::t('app', 'Separate multiple email addresses with comma')]) ?>
    <?= $form->field($model, 'message')->textarea(['placeholder' => 'e.g. The password is...']) ?>

    <?=
        $form->field($model, 'allowComments')->checkbox(['class' => 'preview-links-toggle'])
            ->hint(
                '<div class="preview-links-wrapper hint">
                    ' . Yii::t('app', 'URL that will be send') . ':
                    <a href="' . $viewUrl . '" class="preview-link view" target="_blank" data-cursor-tooltip="' . Yii::t('app', 'Open link in new tab') . '">' . $viewUrl . '</a>
                    <a href="' . $viewAndCommentUrl . '" class="preview-link view-and-comment" target="_blank" data-cursor-tooltip="' . Yii::t('app', 'Open link in new tab') . '">' . $viewAndCommentUrl . '</a>
                    <i class="ion ion-link"></i>
                </div>'
            );
    ?>

    <div class="block text-center">
        <button class="btn btn-primary btn-cons btn-loader"><?= Yii::t('app', 'Send') ?></button>
    </div>
<?php CActiveForm::end(); ?>
