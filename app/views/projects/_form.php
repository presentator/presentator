<?php
use yii\helpers\Html;
use common\models\Project;
use common\widgets\CActiveForm;

/**
 * @var $model        \app\models\ProjectForm
 * @var $typesList    array
 * @var $subtypesList array
 * @var $isNewRecord  boolean
 */

if (!isset($isNewRecord)) {
    $isNewRecord = true;
}

if ($isNewRecord) {
    $formId = 'project_create_form';
} else {
    $formId = 'project_update_form';
}
?>
<?php $form = CActiveForm::begin(['id' => $formId]); ?>
    <?= $form->field($model, 'title') ?>

    <div class="bg-light-grey clear-after out-expand m-b-30 padded p-t-20 p-b-0">
        <?=
            $form->field($model, 'type', [
                'options'  => ['class' => 'block'],
                'template' => '{label}<div class="check-block m-b-30">{input}{error}{hint}</div>'
            ])
            ->radioList($typesList, [
                'class' => ['row'],
                'item' => function($index, $label, $name, $checked, $value) {
                    $radioId = 'project_type_' . $value;
                    $label   = Yii::t('app', 'For {projectType}', ['projectType' => $label]);

                    if ($value == Project::TYPE_TABLET) {
                        $icon = '<i class="ion ion-ipad"></i>';
                    } elseif ($value == Project::TYPE_MOBILE) {
                        $icon = '<i class="ion ion-iphone"></i>';
                    } else {
                        $icon = '<i class="ion ion-monitor"></i>';
                    }

                    return $return = '
                        <div class="cols-4">
                            <div class="check-item">
                                ' . sprintf('<input type="radio" id="%s" name="%s" value="%s" %s>',
                                    $radioId,
                                    $name,
                                    $value,
                                    ($checked ? 'checked' : '')
                                ) . '
                                <label for="' . $radioId . '" class="check-label">
                                    <span class="icon">' . $icon . '</span>
                                    <span class="txt">' . $label . '</span>
                                </label>
                            </div>
                        </div>
                    ';
                },
            ]);
        ?>

        <?=
            $form->field($model, 'subtype', [
                'template' => '{label}{input}',
                'options' => ['class' => 'form-group inline-options']
            ])
            ->dropDownList($subtypesList, [
                'data-default' => [
                    Project::TYPE_TABLET => 21,
                    Project::TYPE_MOBILE => 31,
                ],
            ])
            ->label(false);
        ?>
    </div>

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
            <?= $form->field($model, 'password', ['options' => ['class' => 'form-group m-b-20']])
                ->passwordInput(['placeholder' => Yii::t('app', 'Type your password here...')])
                ->label(false)
            ?>
        </div>
    </div>

    <div class="block text-center">
        <button class="btn btn-primary btn-cons m-t-10 btn-loader">
            <?php if ($isNewRecord): ?>
                <?= Yii::t('app', 'Create project') ?>
            <?php else: ?>
                <?= Yii::t('app', 'Save changes') ?>
            <?php endif ?>
        </button>
    </div>
<?php CActiveForm::end(); ?>
