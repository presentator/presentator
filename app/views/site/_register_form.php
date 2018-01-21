<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\RegisterForm
 */
?>

<?php $form = CActiveForm::begin([
    'action' => Url::to(['site/entrance', '#' => 'register']),
    'id' => 'register_form'
]); ?>
    <?= $form->field($model, 'email') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'passwordConfirm')->passwordInput() ?>
    <?= $form->field($model, 'terms')->checkbox()
        ->label(Yii::t('app', 'I have read and agree with the {termsLink}', [
            'termsLink' => Html::a(Yii::t('app', 'Terms and Conditions'), Yii::$app->params['termsUrl'], ['target' => '_blank'])
        ]))
    ?>

    <div class="block text-center">
        <button class="btn btn-success btn-cons"><?= Yii::t('app', 'Register') ?></button>
    </div>
<?php CActiveForm::end(); ?>
