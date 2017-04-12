<?php
use common\models\Screen;
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\ScreenSettingsForm
 */

?>
<?php $form = CActiveForm::begin(); ?>
    <?= $form->field($model, 'title') ?>

    <div class="row">
        <div class="cols-6">
            <?=
                $form->field($model, 'alignment', [
                    'options'  => ['class' => 'block'],
                    'template' => '{label}<div class="check-block compact m-b-30">{input}{error}{hint}</div>'
                ])
                ->radioList(Screen::getAlignmentLabels(), [
                    'class' => ['row'],
                    'item' => function($index, $label, $name, $checked, $value) {
                        $radioId = 'screen_alignment' . $value;

                        return $return = '
                            <div class="cols-4 check-item">
                                ' . sprintf('<input type="radio" id="%s" name="%s" value="%s" %s>',
                                    $radioId,
                                    $name,
                                    $value,
                                    ($checked ? 'checked' : '')
                                ) . '
                                <label for="' . $radioId . '" class="check-label">
                                    <span class="txt">' . $label . '</span>
                                </label>
                            </div>
                        ';
                    },
                ]);
            ?>
        </div>
        <div class="cols-6">
            <?= $form->field($model, 'background')->input('text', [
                'class' => 'form-control color-picker-input',
                // 'readonly' => 'readonly',
            ]) ?>
        </div>
    </div>

    <div class="block text-center">
        <button class="btn btn-primary btn-cons m-t-10 btn-loader"><?= Yii::t('app', 'Save changes') ?></button>
    </div>
<?php CActiveForm::end(); ?>
