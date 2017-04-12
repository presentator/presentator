<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\widgets\CActiveForm;

/**
 * @var $model \app\models\LoginForm
 */

$forgottenPasswordLink = Html::a(Yii::t('app', 'Forgotten password?'), ['site/forgotten-password'], [
    'class' => 'hint-link forgotten-password',
]);
?>

<?php $form = CActiveForm::begin([
    'action' => Url::to(['site/entrance', '#' => 'login']),
    'id' => 'login_form',
]); ?>
    <?= $form->field($model, 'email') ?>
    <?= $form->field($model, 'password', [
        'template' => '{label}{input}' . $forgottenPasswordLink . '{error}{hint}'
    ])->passwordInput() ?>

    <div class="block text-center">
        <button class="btn btn-success btn-cons"><?= Yii::t('app', 'Login') ?></button>
    </div>
<?php CActiveForm::end(); ?>
