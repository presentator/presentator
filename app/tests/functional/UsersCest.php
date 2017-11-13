<?php
namespace app\tests\functional;

use Yii;
use app\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;

/**
 * @todo add avatar upload/delete tests
 *
 * UsersController functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersCest
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

/* Settings action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function settingsPage(FunctionalTester $I)
    {
        $I->wantTo('Check if settings page is rendered correctly');
        $I->cantAccessAsGuest(['users/settings']);
        $I->amLoggedInAs(1002);
        $I->amOnPage(['users/settings']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['users/settings']);
        $I->see('Settings');
        $I->seeElement('#user_profile_form');
        $I->seeElement('#user_password_form');
        $I->seeElement('#user_notifications_form');
    }

/* Notifications form persist action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxNotificationsSaveFail(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'notifications' => 'invalid',
            'mentions'      => 'invalid',
        ];

        $I->wantTo('Fail saving notification settings form');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-notifications-save']);
        $I->sendAjaxPostRequest(['users/ajax-notifications-save'], [
            'UserNotificationsForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":');
        $I->seeResponseContains('"usernotificationsform-notifications":');
        $I->seeResponseContains('"usernotificationsform-mentions":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxNotificationsSaveSuccess(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'notifications' => true,
            'mentions'      => true,
        ];

        $I->wantTo('Successfully save notification settings form');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-notifications-save']);
        $I->sendAjaxPostRequest(['users/ajax-notifications-save'], [
            'UserNotificationsForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
    }

/* Password form persist action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxPasswordSaveFail(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'oldPassword'        => '1234',
            'newPassword'        => 'asdf',
            'newPasswordConfirm' => '789456',
        ];

        $I->wantTo('Fail saving password settings form');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-password-save']);
        $I->sendAjaxPostRequest(['users/ajax-password-save'], [
            'UserPasswordForm' => $formData,
        ]);
        $user->refresh();

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":');
        $I->seeResponseContains('"userpasswordform-oldpassword":');
        $I->dontSeeResponseContains('"userpasswordform-newpassword":');
        $I->seeResponseContains('"userpasswordform-newpasswordconfirm":');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxPasswordSaveSuccess(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'oldPassword'        => '123456',
            'newPassword'        => '789456',
            'newPasswordConfirm' => '789456',
        ];

        $I->wantTo('Successfully save password settings form');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-password-save']);
        $I->sendAjaxPostRequest(['users/ajax-password-save'], [
            'UserPasswordForm' => $formData,
        ]);
        $user->refresh();

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
    }

/* Profile form persist action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxProfileSaveFail1(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'firstName' => '1234',
            'lastName'  => '123456',
            'email'     => 'invalid',
            'password'  => '1234',
        ];

        $I->wantTo('Fail saving profile settings form (invalid email and wrong password)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save']);
        $I->sendAjaxPostRequest(['users/ajax-profile-save'], [
            'UserProfileForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":');
        $I->seeResponseContains('"userprofileform-email":');
        $I->seeResponseContains('"userprofileform-password":');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxProfileSaveFail2(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'firstName' => '1234',
            'lastName'  => '123456',
            'email'     => 'test_change@presentator.io',
            'password'  => '1234',
        ];

        $I->wantTo('Fail saving profile settings form (valid email and wrong password)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save']);
        $I->sendAjaxPostRequest(['users/ajax-profile-save'], [
            'UserProfileForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":');
        $I->dontSeeResponseContains('"userprofileform-email":');
        $I->seeResponseContains('"userprofileform-password":');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxProfileSaveFail3(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'firstName' => 'Test',
            'lastName'  => 'Testerov',
            'email'     => 'test5@presentator.io',
            'password'  => '123456',
        ];

        $I->wantTo('Fail saving profile settings form (duplicate email and valid password)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save']);
        $I->sendAjaxPostRequest(['users/ajax-profile-save'], [
            'UserProfileForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
        $I->seeResponseContains('"errors":');
        $I->seeResponseContains('"userprofileform-email":');
        $I->dontSeeResponseContains('"userprofileform-password":');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxProfileSaveSuccess1(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'firstName' => 'Spike',
            'lastName'  => 'Spiegel',
        ];

        $I->wantTo('Successfully save profile settings form (without email change)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save']);
        $I->sendAjaxPostRequest(['users/ajax-profile-save'], [
            'UserProfileForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"userIdentificator":');
        $I->dontSeeEmailIsSent();
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxProfileSaveSuccess2(FunctionalTester $I)
    {
        $user = User::findOne(1002);
        $formData = [
            'firstName' => 'Ipsum',
            'email'     => 'test_change@presentator.io',
            'password'  => '123456',
        ];

        $I->wantTo('Successfully save profile settings form (with email change)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save']);
        $I->sendAjaxPostRequest(['users/ajax-profile-save'], [
            'UserProfileForm' => $formData,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"message":');
        $I->seeResponseContains('"userIdentificator":');
        $I->seeEmailIsSent();
    }

/* Avatar related actions:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarUploadFail(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarUploadSuccess(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarSaveFail(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxTempAvatarSaveSuccess(FunctionalTester $I)
    {
    }
}
