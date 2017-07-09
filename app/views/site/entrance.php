<?php
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\FlashAlert;
use common\widgets\LanguageSwitch;

/**
 * @var $this         \yii\web\View
 * @var $loginForm    \app\models\LoginForm
 * @var $registerForm \app\models\RegisterForm
 */

$this->title = Yii::t('app', 'Login');
?>

<?php $this->beginBlock('before_global_wrapper'); ?>
    <div class="diagonal-bg-wrapper"><span id="diagonal_bg" class="diagonal-bg" style="transform: translate3d(-50%, -50%, 0px) rotate(-42.6deg);"></span></div>
<?php $this->endBlock(); ?>

<div class="table-wrapper full-vh-height">
    <div class="table-cell text-center">
        <a href="<?= Url::to(['site/index']) ?>" class="logo">
            <img src="/images/logo_large_white.png" alt="Presentator logo">
            <div class="txt">Presentator</div>
        </a>
        <div class="clearfix"></div>
        <div id="auth_panel" class="auth-panel">
            <?php if (Yii::$app->session->hasFlash('registerSuccess')): ?>
                <div class="content padded text-center">
                    <h3><?= Yii::t('app', 'Successfully registered!') ?></h3>
                    <p><?= Yii::t('app', 'Check your email for further instructions how to activate your account.') ?></p>
                </div>
            <?php else: ?>
                <?= FlashAlert::widget(['close' => false, 'options' => ['class' => 'no-radius-b-l no-radius-b-r']]) ?>

                <div id="auth_tabs" class="tabs m-t-30">
                    <div class="tabs-header">
                        <div class="tab-item active" data-target="#login"><span class="txt"><?= Yii::t('app', 'Login') ?></span></div>
                        <div class="tab-item" data-target="#register"><span class="txt"><?= Yii::t('app', 'Register') ?></span></div>
                    </div>
                    <div class="tabs-content p-b-0">
                        <div id="login" class="tab-item active">
                            <?= $this->render('_login_form', ['model' => $loginForm]); ?>
                        </div>
                        <div id="register" class="tab-item">
                            <?= $this->render('_register_form', ['model' => $registerForm]); ?>
                        </div>
                    </div>
                </div>
                <footer class="footer text-center">
                    <a href="<?= Url::to(['site/auth', 'authclient' => 'facebook']) ?>"
                        class="facebook-link"
                        data-window="facebookLogin"
                        data-width="990"
                        data-height="700"
                    >
                        <i class="ion ion-social-facebook"></i>
                        <span class="txt"><?= Yii::t('app', 'Enter with Facebook') ?></span>
                    </a>
                </footer>
            <?php endif ?>

            <?= LanguageSwitch::widget(); ?>
        </div>
    </div>
</div>
<div id="terms_popup" class="popup popup-large">
    <div class="popup-content">
        <h3 class="popup-title"><?= Yii::t('app', 'Terms and Conditions') ?></h3>
        <span class="popup-close close-icon"></span>
        <div class="content">
            <?php if (Yii::$app->language === 'bg-BG'): ?>
                <h5>Въведение</h5>
                <p>
                    Използвайки уеб сайта Presentator и всички услуги свързани с него, Вие се съгласявате със следните общи условия, включително и последващи промени по тях. Ако не сте съгласни с условията, моля не използвайте Presentator.
                <p>

                <h5>Условия</h5>
                <p>
                    Можем да променим или спрем нашите услуги по всяко време и без причина. При евентуално такова събитие или промяна на условията, ще информираме всеки регистриран потребител за статуса на нашите услуги или промени по условията.
                </p>
                <p>
                    Имаме правото, но не сме задължени, да изтриваме потребители и материали, чието съдържание е незаконно, обидно, заплашително, незенцурно или друго неподходящо и неприлично съдържание.
                </p>
                <p>
                    Ще изтриваме всякакви материали, които противоречат на нечие авторско право и интелектуална собственост.
                </p>

                <h5>Лична информация и данни</h5>
                <p>
                    Presentator съхранява минимална контактна информация (имейл) и съдържанието качено от потребителя.
                </p>
                <p>
                    Не гарантираме backup на Вашите данни. Ако решите да изтриете съдържанието, което сте качили (напр. изтриване на проект или версия), то ще бъде изтрито перманентно от нашите сървъри.
                </p>
                <p>
                    Вашият имейл адрес се ползва единствено за осигуряване на достъп до услугите свързани с Presentator и получаването на отзиви относно Вашето съдържание.
                </p>

                <h5>Публичност</h5>
                <p>
                    Presentator може да съдържа места (напр. коментари към блог статия), в които ще можеш
                    да споделиш, комуникираш и дискутираш с останалите потребители дадена информация.
                    <strong>Не препоръчваме споделянето на лична данни по тези канали.</strong> Всяка такава информация ще бъде публично достъпна.
                </p>
            <?php else: ?>
                <h5>Intro</h5>
                <p>
                    By using the Presentator web site and all services related to it, you are agreeing to be bound by the following "Terms and conditions", including any subsequent changes or modifications to them. If you do not agree to these Terms, please do not access the Presentator website or services.
                <p>

                <h5>Conditions</h5>
                <p>
                    We may modify or terminate our services at anytime, for any reason. We will notify each registered account via email for every modification of these Terms.
                </p>
                <p>
                    We may, but have no obligation to, remove accounts and content containing what we determine as unlawful, offensive, threatening, defamatory, obscene or any inappropriate material.
                </p>
                <p>
                    We may remove content that violates any party's intellectual property or these Terms and conditions.
                </p>

                <h5>Personal information and data</h5>
                <p>
                    Presentator collects only contact information(email) and user uploaded content.
                </p>
                <p>
                    We don't guarantee backups of your data. If you decide to remove your content (for example - deleting a project or version) it will be deleted permanently from our servers.
                </p>
                <p>
                    Your contact information(email) is used only for granting credentials to the Presentator services and receiving feedback for updates related to your content.
                </p>

                <h5>Interactions With Others</h5>
                <p>
                    The Service may contain areas where you may be able to publicly post information, communicate with others such as discussion boards or blogs, review and submit media content. Any information, including Personal Information, that you post there will be public, and therefore anyone who accesses such postings will have the ability to read, collect and further disseminate such information.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('/js/entrance.view.js?v=1499582249');
$this->registerJs('
    var entrance = new EntranceView();
', View::POS_READY, 'entrance-js');
