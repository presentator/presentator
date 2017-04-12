<?php
use common\widgets\CActiveForm;

/**
 * @var $model \common\models\ProjectAccessForm
 */
?>

<?php $form = CActiveForm::begin([
    'id' => 'project_access_form',
]); ?>
    <div class="info-block m-b-30">
        <a href="" class="logo margin-bottom-30" target="_blank" title="Presentator.io">
            <img src="<?= Yii::getAlias('@web/images/logo_large.png') ?>" alt="Presentator logo">
        </a>
        <div class="clearfix m-b-15"></div>
        <p>
            <?= Yii::t('app', 'This project is password protected.') ?> <br>
            <?= Yii::t('app', 'Please type the project password to invoke access.') ?>
        </p>
    </div>


    <?= $form->field($model, 'password', [
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('password'),
            ]
        ])
        ->input('password')
        ->label(false);
    ?>

    <div class="block text-center">
        <button class="btn btn-cons btn-success"><?= Yii::t('app', 'Continue') ?></button>
    </div>
<?php CActiveForm::end(); ?>
