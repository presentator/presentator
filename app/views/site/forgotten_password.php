<?php
use yii\web\View;
use yii\helpers\Url;
use common\widgets\CActiveForm;
use common\widgets\FlashAlert;
use common\widgets\LanguageSwitch;

/**
 * @var $this  \yii\web\View
 * @var $model \app\models\PasswordResetRequestForm
 */

$this->title = Yii::t('app', 'Forgotten password');
?>

<?php $this->beginBlock('before_global_wrapper'); ?>
    <div class="diagonal-bg-wrapper"><span id="diagonal_bg" class="diagonal-bg" style="transform: translate3d(-50%, -50%, 0px) rotate(-30deg);"></span></div>
<?php $this->endBlock(); ?>

<div class="table-wrapper full-vh-height">
    <div class="table-cell text-center">
        <a href="<?= Url::home() ?>" class="logo">
            <img src="/images/logo_large_white.png" alt="Presentator logo">
            <div class="txt">Presentator</div>
        </a>
        <div class="clearfix"></div>
        <div id="auth_panel" class="auth-panel">
            <?php if (Yii::$app->session->hasFlash('enquirySuccess')): ?>
                <div class="content padded text-center">
                    <h3><?= Yii::t('app', 'Successfully sent!') ?></h3>
                    <p><?= Yii::t('app', 'Check your email for further instructions how to reset your password.') ?></p>
                </div>
            <?php else: ?>
                <?= FlashAlert::widget(['close' => false, 'options' => ['class' => 'no-radius-b-l no-radius-b-r']]) ?>

                <div class="content padded text-center">
                    <h3><?= Yii::t('app', 'Forgotten password') ?></h3>

                    <?php $form = CActiveForm::begin(['id' => 'forgotten_password_form']); ?>
                        <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

                        <div class="block text-center">
                            <button class="btn btn-success btn-cons"><?= Yii::t('app', 'Send') ?></button>
                            <div class="block m-t-20 text-small">
                                <a href="<?= Url::to(['site/entrance', '#' => 'login']) ?>">
                                    <i class="ion ion-md-arrow-back"></i>
                                    <span><?= Yii::t('app', 'Back to login') ?></span>
                                </a>
                            </div>
                        </div>
                    <?php CActiveForm::end(); ?>
                </div>
            <?php endif ?>

            <?= LanguageSwitch::widget(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('/js/entrance.view.js?v=1507457981');
$this->registerJs('
    var entrance = new EntranceView();
', View::POS_READY, 'entrance-js');
