<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;

/**
 * UsersController API functional test.
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

    /* ===============================================================
     * `UsersController::actionLogin()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function loginError(FunctionalTester $I)
    {
        $I->wantTo('Fail login an user');

        $I->wantTo('try to login with invalid data');
        $I->sendPost('/users/login', ['email' => 'invalid_email@', 'password' => '']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'    => 'string',
                'password' => 'string',
            ],
        ]);

        $I->amGoingTo('try to login with inactive user');
        $I->sendPost('/users/login', ['email' => 'test1@presentator.io', 'password' => '123456']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'    => 'string',
                'password' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginSuccess(FunctionalTester $I)
    {
        $I->wantTo('Success login an active user');
        $I->sendPost('/users/login', ['email' => 'test2@presentator.io', 'password' => '123456']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"id":1002,"email":"test2@presentator.io"');
        $I->dontSeeResponseContains('"passwordHash":');
        $I->dontSeeResponseContains('"password":');
    }

    /* ===============================================================
     * `UsersController::actionRegister()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function registerError(FunctionalTester $I)
    {
        $I->wantTo('Fail registering a new user');

        $I->sendPOST('/users/register', [
            'email'           => 'invalid_email@',
            'password'        => '123',
            'passwordConfirm' => '12345',
            'firstName'       => 'Test',
            'lastName'        => '',
            'notifications'   => -10,
        ], ['avatar' => Yii::getAlias('@common/tests/_data/test_image.gif')]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'           => 'string',
                'password'        => 'string',
                'passwordConfirm' => 'string',
                'notifications'   => 'string',
                'avatar'          => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function registerSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully register a new user');
        $I->sendPOST('/users/register', [
            'email'         => 'test1232@presentator.io',
            'firstName'     => 'Test',
            'lastName'      => '',
            'notifications' => true,
            'password'        => '123456',
            'passwordConfirm' => '123456',
        ], ['avatar' => Yii::getAlias('@common/tests/_data/test_image.jpg')]);
        $I->seeResponseCodeIs(204);
    }

    /* ===============================================================
     * `UsersController::actionUpdate()` tests
     * ============================================================ */
    /**
     * @param FunctionalTester $I
     */
    public function updateUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to update action');
        $I->seeUnauthorizedAccess('/users/update', 'PUT');
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateError(FunctionalTester $I)
    {
        // Authenticate user
        $user = User::findOne(1002);
        $I->haveHttpHeader('X-Access-Token', $user->generateJwtToken());

        $I->wantTo('Fail updating user model');
        $I->sendPUT('/users/update', [
            'oldPassword'        => '123',
            'newPassword'        => '123',
            'newPasswordConfirm' => '12345',
            'firstName'          => 'Test',
            'lastName'           => '',
            'notifications'      => -10,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'oldPassword'        => 'string',
                'newPassword'        => 'string',
                'newPasswordConfirm' => 'string',
                'notifications'      => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        // Authenticate user
        $user = User::findOne(1002);
        $I->haveHttpHeader('X-Access-Token', $user->generateJwtToken());

        $I->wantTo('Successfully update user model');
        $I->sendPUT('/users/update', [
            'oldPassword'        => '123456',
            'newPassword'        => '654321',
            'newPasswordConfirm' => '654321',
            'firstName'          => 'Test',
            'lastName'           => '',
            'notifications'      => false,
        ]);

        $user->refresh();
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id'        => $user->id,
            'email'     => $user->email,
            'firstName' => $user->firstName,
            'lastName'  => $user->lastName,
        ]);
        $I->dontSeeResponseContains('"passwordHash":');
        $I->dontSeeResponseContains('"password":');
    }
}
