<?php
use yii\web\View;
use yii\helpers\Url;
use common\widgets\CActiveForm;
use common\widgets\FlashAlert;
use common\widgets\LanguageSwitch;

/**
 * @var $this  \yii\web\View
 * @var $model \app\models\PasswordResetForm
 */

$this->title = Yii::t('app', 'Reset password');
?>

<?php $this->beginBlock('before_global_wrapper'); ?>
    <div class="diagonal-bg-wrapper"><span id="diagonal_bg" class="diagonal-bg"></span></div>
<?php $this->endBlock(); ?>

<div class="table-wrapper full-vh-height">
    <div class="table-cell text-center">
        <a href="<?= Url::home() ?>" class="logo">
            <img src="/images/logo_large_white.png" alt="Presentator logo">
            <div class="txt">Presentator</div>
        </a>
        <div class="clearfix"></div>
        <div id="auth_panel" class="auth-panel">
            <?php if (Yii::$app->session->hasFlash('resetSuccess')): ?>
                <div class="content padded text-center">
                    <h3><?= Yii::t('app', 'Success!') ?></h3>
                    <p><?= Yii::t('app', 'Your password was successfully changed!') ?></p>
                    <div class="clearfix m-t-30"></div>
                    <a href="<?= Url::to(['site/entrance', '#' => 'login']) ?>" class="btn btn-cons btn-success"><?= Yii::t('app', 'Go to login page') ?></a>
                </div>
            <?php else: ?>
                <?= FlashAlert::widget(['close' => false, 'options' => ['class' => 'no-radius-b-l no-radius-b-r']]) ?>

                <div class="content padded text-center">
                    <h3><?= Yii::t('app', 'Reset password') ?></h3>

                    <?php $form = CActiveForm::begin(['id' => 'reset_password_form']); ?>
                        <?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>
                        <?= $form->field($model, 'passwordConfirm')->passwordInput(['autofocus' => true]) ?>

                        <div class="block text-center">
                            <button class="btn btn-success btn-cons"><?= Yii::t('app', 'Save') ?></button>
                        </div>
                    <?php CActiveForm::end(); ?>
                </div>
            <?php endif ?>

            <?= LanguageSwitch::widget(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('/js/entrance.view.js');
$this->registerJs('
    var entrance = new EntranceView();
', View::POS_READY, 'entrance-js');
