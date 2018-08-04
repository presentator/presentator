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
        $I->cantAccessAsSuperUser(['users/settings']);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-notifications-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-notifications-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-password-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-password-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save'], [], false, User::TYPE_REGULAR);
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
            'email'     => 'test2_change@presentator.io',
            'password'  => '1234',
        ];

        $I->wantTo('Fail saving profile settings form (valid email and wrong password)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save'], [], false, User::TYPE_REGULAR);
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
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save'], [], false, User::TYPE_REGULAR);
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
            'email'     => 'test2_change@presentator.io',
            'password'  => '123456',
        ];

        $I->wantTo('Successfully save profile settings form (with email change)');
        $I->amLoggedInAs($user->id);
        $I->ensureAjaxPostActionAccess(['users/ajax-profile-save'], [], false, User::TYPE_REGULAR);
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

/* Super user - index action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function indexPage(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);

        $I->wantTo('Check if settings page is rendered correctly');
        $I->cantAccessAsGuest(['users/index']);
        $I->cantAccessAsRegularUser(['users/index']);
        $I->amLoggedInAs($user);
        $I->amOnPage(['users/index']);
        $I->seeResponseCodeIs(200);
        $I->seeCurrentUrlEquals(['users/index']);
        $I->see('Users');
        $I->seeElement('#users_list');
        $I->seeElement('#users_search_list');
    }

/* Super user - ajax search action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function ajaxSearchUsersFail(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);

        $I->wantTo('Fail searching for users');
        $I->amLoggedInAs($user);
        $I->ensureAjaxGetActionAccess(['users/ajax-search-users'], ['search' => 'John'], false, User::TYPE_SUPER);

        $I->amGoingTo('try to search for users with invalid search term length');
        $I->sendAjaxGetRequest(['users/ajax-search-users'], ['search' => 'a'], false, User::TYPE_SUPER);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":false');
    }

    /**
     * @param FunctionalTester $I
     */
    public function ajaxSearchUsersSuccess(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);

        $I->wantTo('Successfully search for users');
        $I->amLoggedInAs($user);
        $I->ensureAjaxGetActionAccess(['users/ajax-search-users'], ['search' => 'John'], false, User::TYPE_SUPER);
        $I->sendAjaxGetRequest(['users/ajax-search-users'], ['search' => 'John'], false, User::TYPE_SUPER);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('"listHtml":');
    }

/* Super user - create action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function createFail(FunctionalTester $I)
    {
        $user     = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);
        $oldCount = User::find()->count();

        $I->wantTo('Fail creating new user');
        $I->cantAccessAsGuest(['users/create']);
        $I->cantAccessAsRegularUser(['users/create']);
        $I->amLoggedInAs($user);

        $I->amGoingTo('submit empty form');
        $I->amOnPage(['users/create']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', ['SuperUserForm' => []]);
        $I->seeElement('.field-superuserform-email.has-error');
        $I->seeElement('.field-superuserform-password.has-error');
        $I->dontSeeElement('.field-superuserform-firstname.has-error');
        $I->dontSeeElement('.field-superuserform-lastname.has-error');
        $I->dontSeeElement('.field-superuserform-status.has-error');
        $I->dontSeeElement('.field-superuserform-type.has-error');
        $I->dontSeeElement('.field-superuserform-passwordconfirm.has-error');
        $I->dontSeeElement('.field-superuserform-notifications.has-error');
        $I->dontSeeElement('.field-superuserform-mentions.has-error');
        $I->seeCurrentUrlEquals(['users/create']);
        $I->dontSeeRecordsCountChange(User::className(), $oldCount);

        $I->amGoingTo('submit invalid form data');
        $I->amOnPage(['users/create']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'email'           => 'invalid',
                'status'          => -1,
                'type'            => -1,
                'password'        => '123',
                'passwordConfirm' => '123456',
                'notifications'   => 'invalid',
                'mentions'        => 'invalid',
            ],
        ]);
        $I->seeElement('.field-superuserform-email.has-error');
        $I->seeElement('.field-superuserform-password.has-error');
        $I->seeElement('.field-superuserform-passwordconfirm.has-error');
        $I->seeElement('.field-superuserform-status.has-error');
        $I->seeElement('.field-superuserform-type.has-error');
        $I->seeElement('.field-superuserform-notifications.has-error');
        $I->seeElement('.field-superuserform-mentions.has-error');
        $I->dontSeeElement('.field-superuserform-firstname.has-error');
        $I->dontSeeElement('.field-superuserform-lastname.has-error');
        $I->seeCurrentUrlEquals(['users/create']);
        $I->dontSeeRecordsCountChange(User::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);

        $I->wantTo('Successfully create new user');
        $I->cantAccessAsGuest(['users/create']);
        $I->cantAccessAsRegularUser(['users/create']);
        $I->amLoggedInAs($user);
        $I->amOnPage(['users/create']);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'email'           => 'create_test@presentator.io',
                'status'          => User::STATUS_INACTIVE,
                'type'            => User::TYPE_REGULAR,
                'firstName'       => 'Test',
                'lastName'        => 'Testerov',
                'password'        => '1234',
                'passwordConfirm' => '1234',
                'notifications'   => true,
                'mentions'        => false,
            ],
        ]);
        $I->dontSeeElement('#super_user_form .has-error');
        $I->seeFlash('success');
        $I->seeCurrentUrlEquals(['users/index']);
        $I->grabRecord(User::className(), ['email' => 'create_test@presentator.io']);
    }

/* Super user - update action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function updateFail(FunctionalTester $I)
    {
        $user       = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);
        $updateUser = User::findOne(1001);

        $I->wantTo('Fail updating an existing user');
        $I->cantAccessAsGuest(['users/update', 'id' => $updateUser->id]);
        $I->cantAccessAsRegularUser(['users/update', 'id' => $updateUser->id]);
        $I->amLoggedInAs($user);

        $I->amGoingTo('submit invalid form data');
        $I->amOnPage(['users/update', 'id' => $updateUser->id]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'email'           => 'invalid@',
                'firstName'       => '',
                'lastName'        => '',
                'status'          => -1,
                'type'            => -1,
                'changePassword'  => true,
                'password'        => '123',
                'passwordConfirm' => '123456',
                'notifications'   => 'invalid',
                'mentions'        => 'invalid',
            ],
        ]);
        $I->seeElement('.field-superuserform-email.has-error');
        $I->seeElement('.field-superuserform-password.has-error');
        $I->seeElement('.field-superuserform-passwordconfirm.has-error');
        $I->seeElement('.field-superuserform-status.has-error');
        $I->seeElement('.field-superuserform-type.has-error');
        $I->seeElement('.field-superuserform-notifications.has-error');
        $I->seeElement('.field-superuserform-mentions.has-error');
        $I->dontSeeElement('.field-superuserform-firstname.has-error');
        $I->dontSeeElement('.field-superuserform-lastname.has-error');
        $I->seeCurrentUrlEquals(['users/update', 'id' => $updateUser->id]);
        $I->assertEquals(User::findOne($updateUser->id), $updateUser);

        $I->amGoingTo('submit duplicated email form data');
        $I->amOnPage(['users/update', 'id' => $updateUser->id]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'email'          => 'test2@presentator.io',
                'changePassword' => false,
            ],
        ]);
        $I->seeElement('.field-superuserform-email.has-error');
        $I->dontSeeElement('.field-superuserform-status.has-error');
        $I->dontSeeElement('.field-superuserform-type.has-error');
        $I->dontSeeElement('.field-superuserform-notifications.has-error');
        $I->dontSeeElement('.field-superuserform-mentions.has-error');
        $I->dontSeeElement('.field-superuserform-password.has-error');
        $I->dontSeeElement('.field-superuserform-passwordconfirm.has-error');
        $I->dontSeeElement('.field-superuserform-firstname.has-error');
        $I->dontSeeElement('.field-superuserform-lastname.has-error');
        $I->seeCurrentUrlEquals(['users/update', 'id' => $updateUser->id]);
        $I->assertEquals(User::findOne($updateUser->id), $updateUser);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);
        $updateUser = User::findOne(1001);

        $I->wantTo('Successfully update new user');
        $I->cantAccessAsGuest(['users/update', 'id' => $updateUser->id]);
        $I->cantAccessAsRegularUser(['users/update', 'id' => $updateUser->id]);
        $I->amLoggedInAs($user);

        $I->amGoingTo('update without changing the user password');
        $I->amOnPage(['users/update', 'id' => $updateUser->id]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'email'           => 'update_test@presentator.io',
                'status'          => User::STATUS_INACTIVE,
                'type'            => User::TYPE_SUPER,
                'firstName'       => 'Test',
                'lastName'        => 'Testerov',
                'changePassword'  => false,
                'notifications'   => false,
                'mentions'        => true,
            ],
        ]);
        $I->dontSeeElement('#super_user_form .has-error');
        $I->seeFlash('success');
        $I->seeCurrentUrlEquals(['users/index']);
        $updateUser = $I->grabRecord(User::className(), ['email' => 'update_test@presentator.io']);
        $I->assertTrue($updateUser->validatePassword('123456'));

        $I->amGoingTo('update with changing the user password');
        $I->amOnPage(['users/update', 'id' => $updateUser->id]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#super_user_form', [
            'SuperUserForm' => [
                'changePassword'  => true,
                'password'        => '1234',
                'passwordConfirm' => '1234',
            ],
        ]);
        $I->dontSeeElement('#super_user_form .has-error');
        $I->seeFlash('success');
        $I->seeCurrentUrlEquals(['users/index']);
        $updateUser->refresh();
        $I->assertTrue($updateUser->validatePassword('1234'));
    }

/* Super user - delete action:
------------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function deleteFail(FunctionalTester $I)
    {
        $user     = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);
        $oldCount = User::find()->count();

        $I->wantTo('Fail deleting user');
        $I->cantAccessAsGuest(['users/delete', 'id' => 1001]);
        $I->cantAccessAsRegularUser(['users/delete', 'id' => 1001]);
        $I->amLoggedInAs($user);

        $I->amGoingTo('try to delete a nonexisting user model');
        $I->sendPOST(['users/delete', 'id' => 12345]);
        $I->seeResponseCodeIs(404);
        $I->dontSeeRecordsCountChange(User::className(), $oldCount);

        $I->amGoingTo('try to delete the current logged in user model');
        $I->sendPOST(['users/delete', 'id' => $user->id]);
        $I->seeResponseCodeIs(200);
        $I->seeFlash('error');
        $I->dontSeeRecordsCountChange(User::className(), $oldCount);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $user = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);

        $I->wantTo('Successfully delete a user model');
        $I->cantAccessAsGuest(['users/delete', 'id' => 1001]);
        $I->cantAccessAsRegularUser(['users/delete', 'id' => 1001]);
        $I->amLoggedInAs($user);
        $I->sendPOST(['users/delete', 'id' => 1001]);
        $I->dontSeeRecord(User::className(), ['id' => 1001]);
    }
}
