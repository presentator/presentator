<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;

/**
 * SiteController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SiteCest
{
    /**
     * @inheritdoc
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

/* Index/Dashboard home action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function dashboard(FunctionalTester $I)
    {
        $I->wantTo('Check if the dashboard is rendered correctly');
        $I->amLoggedInAs(1004);
        $I->amOnPage(['site/index']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['site/index']);
        $I->see('Dashboard');
    }

/* Logout action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function logout(FunctionalTester $I)
    {
        $I->wantTo('Logout a logged in user');
        $I->amLoggedInAs(1002);
        $I->amOnPage(['site/index']);
        $I->seeResponseCodeIs(200);
        $I->sendPOST(['site/logout']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['site/entrance']);
        $I->seeFlash('info');
    }

/* Entrance action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function entranceGeneral(FunctionalTester $I)
    {
        $I->wantTo('Check entrance tabs and links');
        $I->amOnPage(['site/entrance']);
        $I->seeResponseCodeIs(200);

        $I->expectTo('see entrance tabs');
        $I->seeElement('.tabs-header .tab-item[data-target="#login"]');
        $I->seeElement('.tabs-header .tab-item[data-target="#register"]');
        $I->seeElement('.tabs-content .tab-item#login');
        $I->seeElement('.tabs-content .tab-item#register');

        $I->expectTo('see register form elements');
        $I->seeElement('[name="RegisterForm[email]"]');
        $I->seeElement('[name="RegisterForm[password]"]');
        $I->seeElement('[name="RegisterForm[terms]"]');

        $I->expectTo('see login form elements');
        $I->seeElement('[name="LoginForm[email]"]');
        $I->seeElement('[name="LoginForm[password]"]');
        $I->seeElement('a.forgotten-password');
        $I->seeElement('a.facebook-link');
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginFail(FunctionalTester $I)
    {
        $I->wantTo('Fail login');
        $I->amOnPage(['site/entrance']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#login_form', [
            'LoginForm' => [
                'email'    => 'invalid_email@',
                'password' => '',
            ],
        ]);
        $I->seeElement('.field-loginform-email.has-error');
        $I->seeElement('.field-loginform-password.has-error');
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginSuccess(FunctionalTester $I)
    {
        $I->wantTo('Success login');
        $I->amOnPage(['site/entrance']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#login_form', [
            'LoginForm' => [
                'email'    => 'test2@presentator.io',
                'password' => '123456',
            ],
        ]);
        $I->dontSeeElement('#login_form .has-error');
        $I->seeCurrentUrlEquals(['site/index']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function registerFail(FunctionalTester $I)
    {
        $I->wantTo('Fail register');
        $I->amOnPage(['site/entrance']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#register_form', [
            'RegisterForm' => [
                'email'           => 'invalid_email@',
                'password'        => '',
                'passwordConfirm' => '12345',
                'terms'           => false,
            ],
        ]);
        $I->seeElement('.field-registerform-email.has-error');
        $I->seeElement('.field-registerform-password.has-error');
        $I->seeElement('.field-registerform-passwordconfirm.has-error');
        $I->seeElement('.field-registerform-terms.has-error');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function registerSuccess(FunctionalTester $I)
    {
        $I->wantTo('Success register');
        $I->amOnPage(['site/entrance']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#register_form', [
            'RegisterForm' => [
                'email'           => 'dummy_email@presentator.io',
                'password'        => '123456',
                'passwordConfirm' => '123456',
                'terms'           => true,
            ],
        ]);
        $I->seeEmailIsSent();
        $I->dontSeeElement('#register_form .has-error');
        $I->see('Successfully registered!');
    }

/* Activation action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function activationWithMissingToken(FunctionalTester $I)
    {
        $I->wantTo('Try activating an user without token');
        $I->amOnPage(['site/activation', 'email' => 'test1@presentator.io']);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
    }

    /**
     * @param FunctionalTester $I
     */
    public function activationInvalidToken(FunctionalTester $I)
    {
        $user = User::findOne(1001);

        $I->wantTo('Activating an user with invalid token');
        $I->amOnPage(['site/activation', 'email' => $user->email, 'token' => 'invalid_token']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['site/entrance']);
        $I->see('The activation token seems to be invalid or the account is already activated.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function activationActivatedToken(FunctionalTester $I)
    {
        $user = User::findOne(1002);

        $I->wantTo('Try to activate an already activated account');
        $I->amOnPage(['site/activation', 'email' => $user->email, 'token' => $user->getActivationToken()]);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['site/entrance']);
        $I->see('The activation token seems to be invalid or the account is already activated');
    }

    /**
     * @param FunctionalTester $I
     */
    public function activationSuccess(FunctionalTester $I)
    {
        $user = User::findOne(1001);

        $I->wantTo('Activating an inactive account');
        $I->amOnPage(['site/activation', 'email' => $user->email, 'token' => $user->getActivationToken()]);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['site/index']);
    }

/* Forgotten password action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function forgottenPasswordFail(FunctionalTester $I)
    {
        $I->wantTo('Fail sending a forgotten password request');
        $I->amOnPage(['site/forgotten-password']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#forgotten_password_form', [
            'PasswordResetRequestForm' => [
                'email' => 'invalid_email@',
            ],
        ]);
        $I->seeElement('.field-passwordresetrequestform-email.has-error');
        $I->dontSeeFlash('enquirySuccess');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function forgottenPasswordResubmit(FunctionalTester $I)
    {
        $I->wantTo('Send a forgotten password request to user with still valid reset token');
        $I->amOnPage(['site/forgotten-password']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#forgotten_password_form', [
            'PasswordResetRequestForm' => [
                'email' => 'test4@presentator.io',
            ],
        ]);
        $I->dontSeeElement('#forgotten_password_form .has-error');
        $I->seeFlash('info');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function forgottenPasswordSuccess(FunctionalTester $I)
    {
        $I->wantTo('Succes send a forgotten password request');
        $I->amOnPage(['site/forgotten-password']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#forgotten_password_form', [
            'PasswordResetRequestForm' => [
                'email' => 'test2@presentator.io',
            ],
        ]);
        $I->dontSeeElement('#forgotten_password_form .has-error');
        $I->seeFlash('enquirySuccess');
        $I->seeEmailIsSent();
    }


/* Reset password action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function resetPasswordInvalidToken(FunctionalTester $I)
    {
        $user = User::findOne(1003); // user with expired token

        $I->wantTo('Access reset password page with invalid/expired token');
        $I->amOnPage(['site/reset-password', 'token' => $user->passwordResetToken]);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
    }

    /**
     * @param FunctionalTester $I
     */
    public function resetPasswordFail(FunctionalTester $I)
    {
        $user = User::findOne(1004);

        $I->wantTo('Fail resetting user password');
        $I->amOnPage(['site/reset-password', 'token' => $user->passwordResetToken]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#reset_password_form', [
            'PasswordResetForm' => [
                'password'        => '',
                'passwordConfirm' => '654321',
            ],
        ]);
        $I->seeElement('.field-passwordresetform-password.has-error');
        $I->seeElement('.field-passwordresetform-passwordconfirm.has-error');
        $I->dontSeeFlash('resetSuccess');
    }

    /**
     * @param FunctionalTester $I
     */
    public function resetPasswordSuccess(FunctionalTester $I)
    {
        $user = User::findOne(1004);

        $I->wantTo('Success resetting user password');
        $I->amOnPage(['site/reset-password', 'token' => $user->passwordResetToken]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#reset_password_form', [
            'PasswordResetForm' => [
                'password'        => '123456',
                'passwordConfirm' => '123456',
            ],
        ]);
        $I->dontSeeElement('#reset_password_form .has-error');
        $I->seeFlash('resetSuccess');
    }

/* Change email action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function changeEmailInactiveUser(FunctionalTester $I)
    {
        $user = User::findOne(1001); // inactive user

        $I->wantTo('Change the email address for an inactive user');
        $I->amOnPage(['site/change-email', 'token' => $user->emailChangeToken, 'email' => 'test_change@presentator.io']);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
        $I->see('Invalid or expired email change token.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function changeEmailMissingToken(FunctionalTester $I)
    {
        $user = User::findOne(1002); // user with no email change token

        $newEmail         = 'test_change@presentator.io';
        $emailChangeToken = md5($newEmail) . '_' . time();

        $I->wantTo('Change the email address for a user with no emailChangeToken set');
        $I->amOnPage(['site/change-email', 'token' => $emailChangeToken, 'email' => $newEmail]);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
        $I->see('Invalid or expired email change token.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function changeEmailExpiredToken(FunctionalTester $I)
    {
        $user = User::findOne(1003); // user with expired email change token

        $I->wantTo('Change the email address for a user with expired emailChangeToken');
        $I->amOnPage(['site/change-email', 'token' => $user->emailChangeToken, 'email' => 'test_change@presentator.io']);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
        $I->see('Invalid or expired email change token.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function changeEmailDuplicateEmailAddress(FunctionalTester $I)
    {
        $user = User::findOne(1004);

        $I->wantTo('Change the email address for a user with expired emailChangeToken');
        $I->amOnPage(['site/change-email', 'token' => $user->emailChangeToken, 'email' => 'test5@presentator.io']);
        $I->seeResponseCodeIs(400);
        $I->see('Bad Request');
        $I->see('The email test5@presentator.io seems to be already registered');
    }

    /**
     * @param FunctionalTester $I
     */
    public function changeEmailSuccess(FunctionalTester $I)
    {
        $user = User::findOne(1005);

        $I->wantTo('Successfully change user email address');
        $I->amOnPage(['site/change-email', 'token' => $user->emailChangeToken, 'email' => 'test_change2@presentator.io']);
        $I->seeResponseCodeIs(200);
        $I->seeFlash('success');
    }
}
