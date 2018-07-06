<?php
use common\models\Version;

/**
 * @var $form  \common\widgets\CActiveForm
 * @var $model \yii\base\Model
 */

$typesList = Version::getTypeLabels();

$subtypesList = [
    Version::TYPE_TABLET => Version::getTabletSubtypeLabels(),
    Version::TYPE_MOBILE => Version::getMobileSubtypeLabels(),
];

?>

<div class="bg-light-grey clear-after out-expand m-b-30 padded p-t-20 p-b-0">
    <?=
        $form->field($model, 'type', [
            'options'  => ['class' => 'block'],
            'template' => '{label}<div class="check-block m-b-30">{input}{error}{hint}</div>'
        ])
        ->radioList($typesList, [
            'class' => ['row'],
            'item' => function ($index, $label, $name, $checked, $value) {
                $label   = Yii::t('app', 'For {versionType}', ['versionType' => $label]);
                $radioId = 'version_type_' . $value;

                if ($value == Version::TYPE_TABLET) {
                    $icon = '<i class="ion ion-ios-tablet-portrait"></i>';
                } elseif ($value == Version::TYPE_MOBILE) {
                    $icon = '<i class="ion ion-ios-phone-portrait"></i>';
                } else {
                    $icon = '<i class="ion ion-ios-laptop"></i>';
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
                Version::TYPE_TABLET => 21,
                Version::TYPE_MOBILE => 31,
            ],
        ])
        ->label(false);
    ?>

    <?=
        $form->field($model, 'autoScale', [
                'options' => [
                    'class' => 'form-group',
                    'data-scale-group' => [Version::TYPE_MOBILE, Version::TYPE_TABLET]
                ]
            ])
            ->checkbox()
            ->hint('<i class="ion ion-ios-help-circle" data-cursor-tooltip="' . Yii::t('app', 'Auto scale/fit the uploaded screen to the device width.') . '"></i>', [
                'tag'   => 'span',
                'class' => 'hint-inline-block',
            ]);
    ?>

    <?=
        $form->field($model, 'retinaScale', [
                'options' => [
                    'class' => 'form-group',
                    'data-scale-group' => Version::TYPE_DESKTOP
                ]
            ])
            ->checkbox()
            ->hint('<i class="ion ion-ios-help-circle" data-cursor-tooltip="' . Yii::t('app', 'For 2x pixel density designs.') . '"></i>', [
                'tag'   => 'span',
                'class' => 'hint-inline-block',
            ]);
    ?>
</div>
