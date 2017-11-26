<?php
use common\models\Screen;
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\ScreenSettingsForm
 */

?>
<div id="screen_tabs" class="block tabs">
    <div class="tabs-header no-padding">
        <div class="tab-item active" data-target="#screen_settings"><span class="txt"><?= Yii::t('app', 'Settings') ?></span></div>
        <div class="tab-item" data-target="#screen_image"><span class="txt"><?= Yii::t('app', 'Image') ?></span></div>
    </div>

    <div class="tabs-content p-r-0 p-l-0 p-b-0">
        <div id="screen_settings" class="tab-item active">
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
        </div>

        <!-- Replace screen image tab -->
        <div id="screen_image" class="tab-item">
            <div id="replace_image_container" class="upload-container">
                <div class="loader-wrapper">
                    <div class="loader"></div>
                </div>

                <div class="content dz-message">
                    <i class="ion ion-android-upload"></i>
                    <p><?= Yii::t('app', 'Click or drop here to replace the screen image') ?> <em>(png, jpg)</em></p>
                </div>
            </div>

            <div class="text-small m-t-20">
                <strong>NB</strong>:
                <?= Yii::t('app', 'Replacing with different sized image could result in hotspots and comments position disorder!') ?>
            </div>
        </div>
    </div>
</div>
