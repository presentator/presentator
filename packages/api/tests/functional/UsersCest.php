<?php
namespace presentator\api\tests\functional;

use Yii;
use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserSettingFixture;
use presentator\api\tests\fixtures\UserAuthFixture;
use presentator\api\models\User;
use \Codeception\Stub;

/**
 * UsersController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UsersCest
{
    /**
     * {@inheritdoc}
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserSettingFixture' => [
                'class' => UserSettingFixture::class,
            ],
            'UserAuthFixture' => [
                'class' => UserAuthFixture::class,
            ],
        ]);
    }

    /* `UsersController::actionAuthClientsList()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionAuthClientsList()` test.
     *
     * @param FunctionalTester $I
     */
    public function authClientsList(FunctionalTester $I)
    {
        $I->wantTo('list all configured auth clients');

        // mock registered auth clients data
        Yii::$app->set('authClientCollection', [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class'        => 'yii\authclient\clients\Google',
                    'clientId'     => 'test_id',
                    'clientSecret' => 'test_secret',
                ],
                'facebook' => [
                    'class'        => 'yii\authclient\clients\Facebook',
                    'clientId'     => 'test_id',
                    'clientSecret' => '', // simulate missing configuration
                ],
                'twitter' => [
                    'class'        => 'yii\authclient\clients\TwitterOAuth2',
                    'clientId'     => 'test_id',
                    'clientSecret' => 'test_secret',
                ],
            ],
        ]);

        $I->sendGET('/users/auth-clients');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'name'    => 'string',
            'title'   => 'string',
            'state'   => 'string',
            'authUrl' => 'string:url',
        ]);
        $I->seeResponseContainsJson([
            [
                'name'  => 'google',
                'title' => 'Google',
            ],
            [
                'name'  => 'twitter',
                'title' => 'Twitter',
            ],
        ]);
        $I->dontSeeResponseContainsJson([
            [
                'name'  => 'facebook',
                'title' => 'Facebook',
            ],
        ]);
    }

    /* `UsersController::actionAuthClientLogin()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionAuthClientLogin()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function authClientLoginFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully authorize by auth client');

        // mock registered auth clients data
        Yii::$app->set('authClientCollection', [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class'        => 'yii\authclient\clients\Google',
                    'clientId'     => 'test_id',
                    'clientSecret' => '', // simulate missing configuration
                ],
            ],
        ]);

        $I->amGoingTo('submit invalid authorization form data');
        $I->sendPOST('/users/auth-clients', ['client' => 'google', 'code' => '']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'client' => 'string',
                'code'   => 'string',
            ]
        ]);
    }

    /**
     * `UsersController::actionAuthClientLogin()` success test.
     *
     * @param FunctionalTester $I
     */
    public function authClientLoginSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully authorize by auth client');

        // mock registered auth clients data
        $clientStub = Stub::make('yii\authclient\clients\Google', [
            'clientId'          => 'test_id',
            'clientSecret'      => 'test_secret',
            'fetchAccessToken'  => 'test_token',
            'getUserAttributes' => [
                'id'    => 'test_id',
                'email' => 'test3@exampe.com',
            ],
        ]);
        Yii::$app->authClientCollection->setClients(['google' => $clientStub]);

        $I->sendPOST('/users/auth-clients', ['client' => 'google', 'code' => 'test']);
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse();
    }

    /* `UsersController::actionLogin()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionLogin()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function loginFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully sign in');

        $I->amGoingTo('try to login with invalid data');
        $I->sendPOST('/users/login', ['email' => 'invalid_email@', 'password' => '']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array'
        ]);
        $I->seeResponseContains('"errors":{}');

        $I->amGoingTo('try to login with missing user credentials');
        $I->sendPOST('/users/login', ['email' => 'missing@example.com', 'password' => '123456']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array'
        ]);
        $I->seeResponseContains('"errors":{}');

        $I->amGoingTo('try to login with inactive user credentials');
        $I->sendPOST('/users/login', ['email' => 'test1@example.com', 'password' => '123456']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array'
        ]);
        $I->seeResponseContains('"errors":{}');
    }

    /**
     * `UsersController::actionLogin()` success test.
     *
     * @param FunctionalTester $I
     */
    public function loginSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully sign in');

        $I->sendPOST('/users/login', ['email' => 'test2@example.com', 'password' => '123456']);
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse([
            'id'    => 1002,
            'email' => 'test2@example.com',
        ]);
    }

    /* `UsersController::actionRegister()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionRegister()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function registerFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully register a new user');

        $I->amGoingTo('try to register a new user with invalid data types');
        $I->sendPOST('/users/register', [
            'email'               => 'invalid@',
            'password'            => '123',
            'passwordConfirm'     => '123456',
            'firstName'           => str_repeat('.', 256),
            'lastName'            => str_repeat('.', 256),
            'notifyOnEachComment' => 10,
            'notifyOnMention'     => 10,
            'status'              => -1, // for std registration this field should be ignored
            'type'                => -1, // for std registration this field should be ignored
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'               => 'string',
                'password'            => 'string',
                'passwordConfirm'     => 'string',
                'firstName'           => 'string',
                'lastName'            => 'string',
                'notifyOnEachComment' => 'string',
                'notifyOnMention'     => 'string',
            ],
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.status');
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.type');
        $I->dontSeeEmailIsSent();
    }

    /**
     * `UsersController::actionRegister()` success test.
     *
     * @param FunctionalTester $I
     */
    public function registerSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully register a new user');

        $I->sendPOST('/users/register', [
            'email'               => 'create_test@example.com',
            'password'            => '123456',
            'passwordConfirm'     => '123456',
            'firstName'           => 'Test',
            'lastName'            => 'Testerov',
            'notifyOnEachComment' => false,
            'notifyOnMention'     => true,
            'status'              => User::STATUS['ACTIVE'], // for std registration this field should be ignored
            'type'                => User::TYPE['SUPER'],    // for std registration this field should be ignored
        ]);
        $I->seeResponseCodeIs(204);
        $I->seeRecord(User::class, [
            'email'  => 'create_test@example.com',
            'status' => User::STATUS['INACTIVE'],
            'type'   => User::TYPE['REGULAR'],
        ]);
        $I->seeEmailIsSent();
    }

    /* `UsersController::actionActivate()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionActivate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function activateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully activate a user account');

        $I->amGoingTo('try with invalid activation token');
        $I->sendPOST('/users/activate', ['token' => 'invalid_token']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token' => 'string',
            ],
        ]);

        // generate expired activation token
        $user = User::findOne(['status' => User::STATUS['INACTIVE']]);
        $oldTokenDuration = Yii::$app->params['activationTokenDuration'];
        Yii::$app->params['activationTokenDuration'] = -1000;
        $token = $user->generateActivationToken();
        Yii::$app->params['activationTokenDuration'] = $oldTokenDuration;

        $I->amGoingTo('try with expired activation token');
        $I->sendPOST('/users/activate', ['token' => $token]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token' => 'string',
            ],
        ]);
    }

    /**
     * `UsersController::actionActivate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function activateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully activate a user account');

        $user = User::findOne(['status' => User::STATUS['INACTIVE']]);
        $I->sendPOST('/users/activate', ['token' => $user->generateActivationToken()]);
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse([
            'id'     => $user->id,
            'status' => User::STATUS['ACTIVE'],
        ]);
    }

    /* `UsersController::actionRequestPasswordReset()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionRequestPasswordReset()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function requestPasswordResetFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully request a password reset token');

        $I->amGoingTo('try with invalid email');
        $I->sendPOST('/users/request-password-reset', ['email' => 'invalid_email@']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email' => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();
    }

    /**
     * `UsersController::actionRequestPasswordReset()` success test.
     *
     * @param FunctionalTester $I
     */
    public function requestPasswordResetSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully request a password reset token');

        $I->amGoingTo('try with unregistered valid email (fake success)');
        $I->sendPOST('/users/request-password-reset', ['email' => 'missing@example.com']);
        $I->seeResponseCodeIs(204);
        $I->dontSeeRecord(User::class, ['email' => 'missing@example.com']);
        $I->dontSeeEmailIsSent();

        $user = User::findOne(1002);
        $I->amGoingTo('try with registered valid user email that has a valid password reset token (fake success)');
        $I->sendPOST('/users/request-password-reset', ['email' => $user->email]);
        $I->seeResponseCodeIs(204);
        $I->dontSeeEmailIsSent();
        $I->seeRecord(User::class, ['email' => $user->email, 'passwordResetToken' => $user->passwordResetToken]); // no token change

        $user = User::findOne(1003);
        $I->amGoingTo('try with registered valid user email with expired or missing password reset token (real success)');
        $I->sendPOST('/users/request-password-reset', ['email' => $user->email]);
        $I->seeResponseCodeIs(204);
        $I->dontSeeRecord(User::class, ['email' => $user->email, 'passwordResetToken' => $user->passwordResetToken]); // token change
        $I->seeEmailIsSent();
    }

    /* `UsersController::actionConfirmPasswordReset()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionConfirmPasswordReset()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function confirmPasswordResetFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully confirm a password reset request');

        $I->amGoingTo('try with nonexisting token');
        $I->sendPOST('/users/confirm-password-reset', ['token' => 'missing_token']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token'           => 'string',
                'password'        => 'string',
                'passwordConfirm' => 'string',
            ],
        ]);

        $expiredUser = User::findOne(1003);
        $I->amGoingTo('try with expired token');
        $I->sendPOST('/users/confirm-password-reset', ['token' => $expiredUser->passwordResetToken]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token'           => 'string',
                'password'        => 'string',
                'passwordConfirm' => 'string',
            ],
        ]);

        $validUser = User::findOne(1002);
        $I->amGoingTo('try with valid token but invalid passwords');
        $I->sendPOST('/users/confirm-password-reset', [
            'token'           => $validUser->passwordResetToken,
            'password'        => '123',
            'passwordConfirm' => '123456',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'password'        => 'string',
                'passwordConfirm' => 'string',
            ],
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.token');
    }

    /**
     * `UsersController::actionConfirmPasswordReset()` success test.
     *
     * @param FunctionalTester $I
     */
    public function confirmPasswordResetSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully confirm a password reset request');

        $user = User::findOne(1002);
        $I->sendPOST('/users/confirm-password-reset', [
            'token'           => $user->passwordResetToken,
            'password'        => '123456',
            'passwordConfirm' => '123456',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse(['id' => $user->id]);
    }

    /* `UsersController::actionRequestEmailChange()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionRequestEmailChange()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function requestEmailChangeFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully request email change');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/users/request-email-change', ['newEmail' => 'new_email@example.com']);
        $I->seeUnauthorizedResponse();
        $I->dontSeeEmailIsSent();

        $user = User::findOne(1003);
        $I->amGoingTo('authorize');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());

        $I->amGoingTo('try with invalid new email');
        $I->sendPOST('/users/request-email-change', ['newEmail' => 'invalid_email@']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'newEmail' => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();

        $I->amGoingTo('try with existing user email');
        $I->sendPOST('/users/request-email-change', ['newEmail' => 'test1@example.com']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'newEmail' => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();
    }

    /**
     * `UsersController::actionRequestEmailChange()` success test.
     *
     * @param FunctionalTester $I
     */
    public function requestEmailChangeSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully request email change');

        $user = User::findOne(1003);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/users/request-email-change', ['newEmail' => 'new_email@example.com']);
        $I->seeResponseCodeIs(204);
        $I->seeEmailIsSent();
    }

    /* `UsersController::actionConfirmEmailChange()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionConfirmEmailChange()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function confirmEmailChangeFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully confirm a email change request');

        $I->amGoingTo('try with missing token');
        $I->sendPOST('/users/confirm-email-change');
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token' => 'string',
            ],
        ]);

        // generate expired activation token
        $user = User::findOne(['email' => 'test3@example.com']);
        $oldTokenDuration = Yii::$app->params['emailChangeTokenDuration'];
        Yii::$app->params['emailChangeTokenDuration'] = -1000;
        $token = $user->generateEmailChangeToken('new_email@example.com');
        Yii::$app->params['emailChangeTokenDuration'] = $oldTokenDuration;

        $I->amGoingTo('try with expired token');
        $I->sendPOST('/users/confirm-email-change', ['token' => $token]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'token' => 'string',
            ],
        ]);
    }

    /**
     * `UsersController::actionConfirmEmailChange()` success test.
     *
     * @param FunctionalTester $I
     */
    public function confirmEmailChangeSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully confirm a email change request');

        $user = User::findOne(['email' => 'test3@example.com']);
        $I->sendPOST('/users/confirm-email-change', [
            'token' => $user->generateEmailChangeToken('new_email@example.com'),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse([
            'id'    => $user->id,
            'email' => 'new_email@example.com',
        ]);
    }

    /* `UsersController::actionFeedback()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionFeedback()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function feedbackFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully submit a feedback');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/users/feedback');
        $I->seeUnauthorizedResponse();
        $I->dontSeeEmailIsSent();

        $user = User::findOne(1003);
        $I->amGoingTo('authorize');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());

        $I->amGoingTo('submit invalid feedback form data');
        $I->sendPOST('/users/feedback', [
            'message' => '',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'message' => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();
    }

    /**
     * `UsersController::actionFeedback()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function feedbackSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully submit a feedback');

        $user = User::findOne(1003);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/users/feedback', [
            'message' => 'test',
        ]);
        $I->seeResponseCodeIs(204);
        $I->seeEmailIsSent();
    }

    /* `UsersController::actionRefresh()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionRefresh()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function refreshFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully refresh a user token');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/users/refresh');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `UsersController::actionRefresh()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function refreshSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully refresh a user token');

        $user = User::findOne(1003);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/users/refresh');
        $I->seeResponseCodeIs(200);
        $I->seeUserAuthResponse(['id' => $user->id]);
    }

    /* `UsersController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list users');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/users');
        $I->seeUnauthorizedResponse();

        $user = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $I->amGoingTo('try accessing the action as a regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendGET('/users');
        $I->seeForbiddenResponse();
    }

    /**
     * `UsersController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list users');

        $user = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);
        $I->amGoingTo('authorize');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());

        $I->sendAndCheckDataProviderResponses('/users', [
            [
                'params'   => [],
                'expected' => User::find()->orderBy(['createdAt' => SORT_ASC])->all()
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => User::find()->orderBy(['createdAt' => SORT_ASC])->limit(1)->offset(1)->all()
            ],
            [
                'params'   => ['search[type]' => User::TYPE['REGULAR'], 'sort' => '-createdAt'],
                'expected' => User::find()->where(['type' => User::TYPE['REGULAR']])->orderBy(['createdAt' => SORT_DESC])->all()
            ],
        ], function ($scenarioIndex, $scenarioData) use ($I) {
            $I->seeResponseMatchesJsonType([
                'id'       => 'integer',
                'email'    => 'string',
                'avatar'   => 'array',
                'settings' => 'array',
            ]);
            $I->dontSeeResponseContainsUserHiddenFields('*');
        });
    }

    /* `UsersController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view a user');

        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);
        $regularUserA = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $regularUserB = User::find()
            ->where(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']])
            ->andWhere(['!=', 'id', $regularUserA->id])
            ->one();

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/users/' . $regularUserA->id);
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as a regular user and try to view another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUserA->generateAccessToken());
        $I->sendGET('/users/' . $regularUserB->id);
        $I->seeForbiddenResponse();

        $I->amGoingTo('authorize as a super user and try to view unexisting user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/users/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `UsersController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view a user');

        $inactiveUser = User::findOne(['status' => User::STATUS['INACTIVE']]);
        $regularUser  = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as a regular user and try to view itself',
                'token'   => $regularUser->generateAccessToken(),
                'userId'  => $regularUser->id,
            ],
            [
                'comment' => 'authorize as a super user and try to view another user',
                'token'   => $superUser->generateAccessToken(),
                'userId'  => $inactiveUser->id,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/users/' . $scenario['userId']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'       => 'integer:=' . $scenario['userId'],
                'email'    => 'string',
                'avatar'   => 'array',
                'settings' => 'array',
            ]);
            $I->dontSeeResponseContainsUserHiddenFields();
        }
    }

    /* `UsersController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create a new user');

        $regularUser = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/users');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as a regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPOST('/users');
        $I->seeForbiddenResponse();

        $I->amGoingTo('authorize as a super user and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPOST('/users', [
            'email'               => 'invalid@',
            'password'            => '123',
            'passwordConfirm'     => '123456',
            'firstName'           => str_repeat('.', 256),
            'lastName'            => str_repeat('.', 256),
            'notifyOnEachComment' => 10,
            'notifyOnMention'     => 10,
            'status'              => -1,
            'type'                => -1,
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'email'               => 'string',
                'password'            => 'string',
                'passwordConfirm'     => 'string',
                'firstName'           => 'string',
                'lastName'            => 'string',
                'notifyOnEachComment' => 'string',
                'notifyOnMention'     => 'string',
                'status'              => 'string',
                'type'                => 'string',
            ],
        ]);
        $I->dontSeeEmailIsSent();
    }

    /**
     * `UsersController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create a new user');

        $superUser = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as a super user and create new active user',
                'data'    => [
                    'email'               => 'create_test@example.com',
                    'password'            => '123456',
                    'passwordConfirm'     => '123456',
                    'firstName'           => 'Test',
                    'lastName'            => 'Testerov',
                    'notifyOnEachComment' => false,
                    'notifyOnMention'     => true,
                    'status'              => User::STATUS['ACTIVE'],
                    'type'                => User::TYPE['SUPER'],
                ],
                'expectEmail' => false,
            ],
            [
                'comment' => 'authorize as a super user and create new inactive user',
                'data'    => [
                    'email'           => 'create_test2@example.com',
                    'password'        => '123456',
                    'passwordConfirm' => '123456',
                    'status'          => User::STATUS['INACTIVE'],
                    'type'            => User::TYPE['REGULAR'],
                ],
                'expectEmail' => true,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $dataToCheck = [
                'email'     => $scenario['data']['email'] ?? '',
                'firstName' => $scenario['data']['firstName'] ?? '',
                'lastName'  => $scenario['data']['lastName'] ?? '',
                'status'    => $scenario['data']['status'] ?? User::STATUS['INACTIVE'],
                'type'      => $scenario['data']['type'] ?? User::STATUS['REGULAR'],
            ];

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
            $I->sendPOST('/users', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeRecord(User::class, $dataToCheck);
            $I->seeResponseMatchesJsonType([
                'id'       => 'integer',
                'email'    => 'string',
                'avatar'   => 'array',
                'settings' => 'array',
            ]);
            $I->dontSeeResponseContainsUserHiddenFields();
            $I->dontSeeResponseJsonMatchesJsonPath('$.avatar.original');

            if ($scenario['expectEmail']) {
                $I->seeEmailIsSent();
            } else {
                $I->dontSeeEmailIsSent();
            }
        }
    }

    /* `UsersController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update a user');

        $regularUser = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/users/' . $regularUser->id);
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as a regular user and try to update another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/users/' . $superUser->id);
        $I->seeForbiddenResponse();

        $I->amGoingTo('authorize as a super user and try to update unexisting user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/users/123456');
        $I->seeNotFoundResponse();

        $testScenarios = [
            [
                'comment' => 'authorize as a regular user and submit invalid form data',
                'token' => $regularUser->generateAccessToken(),
                'userId' => $regularUser->id,
                'errors' => ['avatar', 'oldPassword', 'newPassword', 'newPasswordConfirm', 'firstName', 'lastName', 'notifyOnEachComment', 'notifyOnMention'],
            ],
            [
                'comment' => 'authorize as a super user and submit invalid form data',
                'token' => $superUser->generateAccessToken(),
                'userId' => $regularUser->id,
                'errors' => ['avatar', 'newPassword', 'newPasswordConfirm', 'firstName', 'lastName', 'notifyOnEachComment', 'notifyOnMention', 'email', 'status', 'type'],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $updateData = $scenario['updateData'] ?? [
                'oldPassword'         => '......', // for super users this field should be ignored
                'newPassword'         => '123',
                'newPasswordConfirm'  => '123456789',
                'firstName'           => str_repeat('.', 256),
                'lastName'            => str_repeat('.', 256),
                'notifyOnEachComment' => 10,
                'notifyOnMention'     => 10,
                'email'               => '', // for regular users this field should be ignored
                'status'              => -1, // for regular users this field should be ignored
                'type'                => -1, // for regular users this field should be ignored
            ];

            $nonErrorFields = array_diff(array_merge(['avatar'], array_keys($updateData)), $scenario['errors']);

            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/users/' . $scenario['userId'], $updateData, [
                'avatar' => Yii::getAlias('@app/tests/_data/test_image.gif'),
            ]);
            $I->seeResponseCodeIs(400);
            $I->seeResponseIsJson();

            $I->seeResponseMatchesJsonType([
                'message' => 'string',
                'errors'  => array_fill_keys($scenario['errors'], 'string'),
            ]);

            foreach ($nonErrorFields as $field) {
                $I->dontSeeResponseJsonMatchesJsonPath('$.errors.' . $field);
            }
        }
    }

    /**
     * `UsersController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update a user');

        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);
        $regularUserA = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $regularUserB = User::find()
            ->where(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']])
            ->andWhere(['!=', 'id', $regularUserA->id])
            ->one();

        $testScenarios = [
            [
                'comment' => 'authorize as a regular user and try to update its data',
                'token'   => $regularUserA->generateAccessToken(),
                'userId'  => $regularUserA->id,
                'updateData'    => [
                    'oldPassword'         => '123456',
                    'newPassword'         => '123456',
                    'newPasswordConfirm'  => '123456',
                    'firstName'           => 'A',
                    'lastName'            => 'B',
                    'notifyOnEachComment' => true,
                    'notifyOnMention'     => false,
                    'email'               => 'new_email@example.com',  // for regular users this field should be ignored
                    'status'              => User::STATUS['INACTIVE'], // for regular users this field should be ignored
                    'type'                => User::TYPE['SUPER'],      // for regular users this field should be ignored
                ],
                'expectData' => [
                    'firstName' => 'A',
                    'lastName'  => 'B',
                    'email'     => $regularUserA->email,
                    'status'    => $regularUserA->status,
                    'type'      => $regularUserA->type,
                ],
            ],
            [
                'comment' => 'authorize as a super user and try to update another user',
                'token'   => $superUser->generateAccessToken(),
                'userId'  => $regularUserB->id,
                'updateData'    => [
                    'newPassword'         => '123456',
                    'newPasswordConfirm'  => '123456',
                    'firstName'           => 'C',
                    'notifyOnEachComment' => true,
                    'notifyOnMention'     => true,
                    'email'               => 'new_email@example.com',  // for regular users this field should be ignored
                    'status'              => User::STATUS['INACTIVE'], // for regular users this field should be ignored
                    'type'                => User::TYPE['SUPER'],      // for regular users this field should be ignored
                ],
                'expectData' => [
                    'firstName' => 'C',
                    'lastName'  => $regularUserB->lastName,
                    'email'     => 'new_email@example.com',
                    'status'    => User::STATUS['INACTIVE'],
                    'type'      => User::TYPE['SUPER'],
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/users/' . $scenario['userId'], $scenario['updateData'], [
                'avatar' => Yii::getAlias('@app/tests/_data/test_image.png'),
            ]);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'     => 'integer',
                'email'  => 'string',
                'avatar' => [
                    'original' => 'string',
                    'small'    => 'string',
                ],
                'settings' => [
                    'notifyOnEachComment' => 'boolean',
                    'notifyOnMention'     => 'boolean',
                ],
            ]);
            $I->seeRecord(User::class, $scenario['expectData']);
            $I->dontSeeResponseContainsUserHiddenFields();
            $I->dontSeeResponseJsonMatchesJsonPath('$.avatarFilePath');
        }
    }

    /* `UsersController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `UsersController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete a user');

        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);
        $regularUserA = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $regularUserB = User::find()
            ->where(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']])
            ->andWhere(['!=', 'id', $regularUserA->id])
            ->one();

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/users/' . $regularUserA->id);
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as a regular user and try to delete another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUserA->generateAccessToken());
        $I->sendDELETE('/users/' . $regularUserB->id);
        $I->seeForbiddenResponse();

        $I->amGoingTo('authorize as a super user and try to delete unexisting user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/users/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `UsersController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete a user');

        $inactiveUser = User::findOne(['status' => User::STATUS['INACTIVE']]);
        $regularUser  = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['REGULAR']]);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as a regular user and try to delete itself',
                'token'   => $regularUser->generateAccessToken(),
                'userId'  => $regularUser->id,
            ],
            [
                'comment' => 'authorize as a super user and try to delete another user',
                'token'   => $superUser->generateAccessToken(),
                'userId'  => $inactiveUser->id,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/users/' . $scenario['userId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(User::class, ['id' => $scenario['userId']]);
        }
    }
}
